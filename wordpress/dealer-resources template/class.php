<?php
/**
 * Dealer Resources Class
 *
 * Used to fill in the components of the Dealer Resources Template
 *
 * Built: October 2022
 * Last Updated: October 2022
 */
class dealerResources
{
  /**
   * @var array|WP_Post|null 
   * @static
   */
  private static $page_object;

  /**
   * @global $post
   */
  public function __construct()
  {
    global $post;

    self::$page_object = $post;
  }

  /**
   * ===========================================================================
   * GETTERS
   * ===========================================================================
   * Private methods that return a simple value to be used inside of a builder function
   */

  /**
   * GET COMPLETION
   *
   * A Somewhat redundant way of getting a completion bool in a more repeatable way
   * Returns TRUE if the page is 'complete' and FALSE if it is not 'complete'
   *
   * @param $page_id
   * @return bool
   * @since v2022.09.1.0
   */
  private function getCompletion( $page_id ): bool
  {
    $isComplete = get_user_meta( get_current_user_id(), "di_course_completion_" . $page_id, TRUE );

    if ( $isComplete )
    // if the user has completed the topic, return true; otherwise return false
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * GET COMPLETION PERCENTAGE
   *
   * runs through all the page's children and descendants to check if they are complete and returns an integer
   * representing the percentage of descendants that are complete.
   *
   * @param $page_id
   * @return int
   * @since v2022.09.1.0
   */
  private function getCompletionPercentage( $page_id ): int
  {
    $total_count = 0;
    $complete_count = 0;

    $children = get_pages( array(
      'child_of' => $page_id,
      'parent' => $page_id,
      'sort_column' => 'menu_order',
      'sort_order' => 'ASC'
    ) );

    foreach ( $children as $child )
    // run through each child of the $page_id
    {
      $descendants = get_pages( array( 'child_of' => $child->ID ) );
      if ( $descendants )
      // if the child is a parent page (has descendants)
      {
        // don't record completion for the parent, as the page is inaccessible from the frontend ui
        foreach ( $descendants as $descendant )
          // run through each $descendant of the child page
        {
          // always increase $total_count
          $total_count++;
          if ( $this->getCompletion($descendant->ID))
          // if the descendant is complete, increase $complete_count
          {
            $complete_count++;
          }
        }
      }
      else
      // if the child is not a parent, record the completion for the child
      {
        // always increase $total_count
        $total_count++;
        if ( $this->getCompletion($child->ID))
        // if the child is complete, increase $complete_count
        {
          $complete_count++;
        }
      }
    }

    return round($complete_count/$total_count*100);
  }

  /**
   * GET TOPIC TAGS
   *
   * Using a topic page's id, get the page's selected tags, filter them, then return them as a string containing the
   * tag's html
   *
   * @param $topic_id
   * @return array
   * @since v2022.09.1.0
   */
  private function getTopicTags( $topic_id ): array
  {
    $tags_array = get_field( 'topic_tags', $topic_id );

    if ( !empty($tags_array) )
    {
      if (in_array("New", $tags_array)
        && in_array("Updated", $tags_array))
        // if the tags contain both "new" AND "updated",
        // prioritize the "new" tag and remove the "updated" tag
      {
        unset($tags_array[array_search("Updated", $tags_array)]);
      }

      if (in_array("Featured", $tags_array)
        && in_array("Essential", $tags_array))
        // if the tags contain both "featured" AND "essential",
        // prioritize the "featured" tag and remove the "essential" tag
      {
        unset($tags_array[array_search("Essential", $tags_array)]);
      }

      if ( (in_array("New", $tags_array) || in_array("Updated", $tags_array))
        && (get_the_modified_time( 'U', $topic_id ) < date('U', strtotime( "-2 month" ) )) )
      // if the tags array contains new or update && the last modified date is older than 2 months
      {
        if (in_array("New", $tags_array))
        // remove "new" from the array
        {
          unset($tags_array[array_search("New", $tags_array)]);
        }
        else
        // remove "updated" from the array
        {
          unset($tags_array[array_search("Updated", $tags_array)]);
        }
      }
    } else {
      $tags_array = [];
    }

    return $tags_array;
  }

  /**
   * GET TOPIC TYPE
   *
   * Based on a topic's id, determines what type of content the topic contains
   *
   * @param $topic_id
   * @return string
   * @since v2022.09.1.0
   */
  private function getTopicType( $topic_id ): string
  {
    $output = "";

    if ( get_field('video_embed', $topic_id )
      && get_post( $topic_id )->post_content )
    // if the topic contains a video and post_content == mixed
    {
      $output .= "mixed";
    }
    elseif ( get_field('video_embed', $topic_id ) )
    // if the topic contains just a video == video
    {
      $output .= "video";
    }
    elseif ( get_post( $topic_id )->post_content )
    // if the topic contains just post_content == text
    {
      $output .= "text";
    }
    elseif( get_page_template_slug( $topic_id ) == "page-templates/redirect-to-link.php" )
    // if the topic is built on the redirect-to-link template == link
    {
      $output .= "link";
    }
    else
    // if topic type doesn't match predefined values, return "other" to visualize the error
    {
      $output .= "other";
    }

    return $output;
  }

  /**
   * GET TOPIC LENGTH
   *
   * Get the estimated length of a given topic in minutes based on an average wpm of 250
   * returns 0 if type is link or unknown
   *
   * @param $topic_id
   * @return false|float|int|mixed
   * @since v2022.09.1.0
   */
  private function getTopicLength( $topic_id )
  {
    $topic_length = 0;

    switch ( $this->getTopicType($topic_id) ) {
      case "mixed":
        // if topic type is mixed, get reading time and video length, add together to update $topic_length
        $text_length = str_word_count( get_post( $topic_id )->post_content );
        $text_time = ceil( $text_length/250 );
        $vid_time = get_field( 'video_length', $topic_id ) ?? 3;
        $topic_length = $text_time + $vid_time;
        break;
      case "text":
        // if topic type is text, get reading time and update $topic_length
        $text_length = str_word_count( get_post( $topic_id )->post_content );
        $topic_length = ceil( $text_length/250 );
        break;
      case "video":
        // if video, get video length and update $topic_length
        $topic_length = get_field( 'video_length', $topic_id ) ?? 3;
        break;
    }

    return $topic_length;
  }

  /**
   * ===========================================================================
   * BUILDERS
   * ===========================================================================
   * Private & Public methods that return more complex strings that may include html
   */

  /**
   * TOPIC TYPE BUILDER
   *
   * Based on the incoming topic's type, outputs a string containing the iconify icon for that content type
   *
   * @param $topic_id
   * @return string
   * @since v2022.09.1.0
   */
  private function topicTypeBuilder( $topic_id ): string
  {
    $output = "";

    $topic_type = $this->getTopicType($topic_id);

    switch( $topic_type ){
      case "mixed":
      case "text":
        // if type is mixed or text, return a text icon
        $output .= "<iconify-icon inline icon=\"material-symbols:chrome-reader-mode-outline-sharp\"></iconify-icon>";
        break;
      case "video":
        // if type is video, return a play icon
        $output .= "<iconify-icon inline icon=\"el:video\"></iconify-icon>";
        break;
      case "link":
        // if type is link, return a hyperlink icon
        $output .= "<iconify-icon inline icon=\"fe:insert-link\"></iconify-icon>";
        break;
      default:
        // if unknown, return a question mark icon
        $output .= "<iconify-icon inline icon=\"carbon:unknown\"></iconify-icon>";
    }

    return $output;
  }

  /**
   * TOPIC TAG BUILDER
   *
   * Uses the array from $this->getTopicTags() to build the html for the topic tags
   *
   * @param $topic_id
   * @return string
   * @since v2022.09.1.0
   */
  private function topicTagBuilder( $topic_id ): string
  {
    $output = "";

    $tags = $this->getTopicTags( $topic_id );

    if ( !empty( $tags ) )
    // if there is content in $tags
    {
      foreach ( $tags as $tag )
      // build out each tag as a div.tag
      {
        $output .= "<div class='tag " . strtolower($tag) . "'>" . ucwords($tag) . "</div>";
      }
    }

    return $output;
  }

  /**
   * TOPIC LENGTH BUILDER
   *
   * Grab the length integer from  $this->getTopicLength(), add the correct unit, return as a string
   *
   * @param $topic_id
   * @return string|null
   * @since v2022.09.1.0
   */
  private function topicLengthBuilder( $topic_id ): ?string
  {
    $topic_length = $this->getTopicLength( $topic_id );


    $compareToSixty = $topic_length/60;

    if ( $compareToSixty < 1 )
    // divide topic_length by 60 to determine if the value less than 1 hour
    {
      if ( $topic_length == 1 )
      // if there is 1 minute, define $unit as singular 'min'
      {
        $unit = " min";
      }
      else
      // if there is more than 1 minute, define $unit as plural 'mins'
      {
        $unit = " mins";
      }
    }
    else
    // if $topic_length is greater than one hour
    {
      // round the topic length to the nearest hour
      $length = round($topic_length / 60);
      if ($length = 1)
      // if $length is equal to 1, define $unit as singular 'hr'
      {
        $unit = " hr";
      }
      else
      // if $length is equal to 1, define $unit as plural 'hrs'
      {
        $unit = " hrs";
      }
    }

    if ( $topic_length = 0 )
    // if topic length is 0, define $length_string as null to identify links and undefined topic types
    {
      $length_string = NULL;
    }
    else
    // if topic length exists, add together $topic_length and $unit to define $length_string
    {
      $length_string = $topic_length . $unit;
    }

    return $length_string;
  }

  /**
   * TOPIC COMPLETE BUILDER
   *
   * Using the $this->getCompletion() value, returns either an unchecked or checked icon in html string
   *
   * @param $topic_id
   * @return string
   * @since v2022.09.1.0
   */
  private function topicCompleteBuilder( $topic_id ): string
  {
    $output = "";

    if ( $this->getCompletion( $topic_id ) )
    // if topic is complete, output a filled checkbox
    {
      $output .= "<iconify-icon inline icon=\"fontisto:checkbox-active\" style='color: var(--green)'></iconify-icon>";
    }
    else
      // if topic is incomplete, output an empty checkbox
    {
      $output .= "<iconify-icon inline icon=\"fontisto:checkbox-passive\"></iconify-icon>";
    }

    return $output;
  }

  /**
   * CHILD CARD BUILDER
   *
   * Build cards that contain information about a child/descendant page that is then fed into topic grid sections
   *
   * @param $child_obj
   * @return string
   * @since v2022.09.1.0
   */
  private function childCardBuilder( $child_obj ): string
  {
    $output = "";
    $permalink = get_permalink( $child_obj->ID );

    if ( empty( $background_image_url = get_field( "image", $child_obj->ID ) ) )
    {
      $background_image_url = str_replace($_SERVER['DOCUMENT_ROOT'], '', get_stylesheet_directory_uri() . "/library/images/default-header-background.jpg");
    }

    // CARD
    $output .= "<div class=\"topic-card\">";

    if ( $this->getTopicType( $child_obj->ID ) == "link" )
      // if the topic page is based on the redirect template
    {
      // open it in a new tab
      $output .=  "<a href=\"$permalink\" target='_blank'>";
    }
    else
    {
      $output .=  "<a href=\"$permalink\">";
    }

    // CARD -> TOP
    $output .=    "<div class=\"top\" style=\"background-image:url($background_image_url)\">";
    $output .=      "<div class='card-tags'>";
    $output .=          $this->topicTagBuilder( $child_obj->ID );
    $output .=      "</div>";
    $output .=      "<div class=\"card-info\">";
    $output .=        "<div class=\"icon\">";
    $output .=          $this->topicTypeBuilder( $child_obj->ID );
    $output .=        "</div>";
    $output .=        "<div class=\"length\">";
    $output .=          $this->topicLengthBuilder( $child_obj->ID );
    $output .=        "</div>";
    $output .=      "</div>";
    $output .=      "<div class='card-overlay'></div>";
    $output .=    "</div>";
    // END: CARD -> TOP

    // CARD -> BOTTOM
    $output .=    "<div class=\"bottom\">";
    $output .=      "<div class='card-tags mobile'>";
    $output .=          $this->topicTagBuilder( $child_obj->ID );
    $output .=      "</div>";
    $output .=      "<div class=\"card-info mobile\">";
    $output .=        "<div class=\"icon\">";
    $output .=          $this->topicTypeBuilder( $child_obj->ID );
    $output .=        "</div>";
    $output .=        "<div class=\"length\">";
    $output .=          $this->topicLengthBuilder( $child_obj->ID );
    $output .=        "</div>";
    $output .=      "</div>";
    $output .=          $this->topicCompleteBuilder( $child_obj->ID );
    $output .=          "<h3>";
    $output .=              $child_obj->post_title;
    $output .=          "</h3>";
    $output .=     "</div>";
    // END: CARD -> BOTTOM
    $output .=   "</a>";
    $output .= "</div>";
    // END: CARD

    return $output;
  }

  /**
   * TOPIC GRID BUILDER
   *
   * Build out the topic grid section of the dealer resources template
   *
   * @return string
   * @since v2022.09.1.0
   */
  public function topicGridBuilder(): string
  {
    $children = get_pages( array(
      'child_of' => self::$page_object->ID,
      'parent' => self::$page_object->ID,
      'sort_column' => 'menu_order',
      'sort_order' => 'ASC'
    ) );

    // Child pages w/o descendants
    $output = "<div class='grid-section'>";
      $output .= "<div class='topic-grid wo-children'>";
      foreach ( $children as $child )
      {
        $descendants = get_pages( array(
          'child_of' => $child->ID,
          'parent' => $child->ID,
          'sort_column' => 'menu_order',
          'sort_order' => 'ASC'
        ) );
        // if the child is not a parent
        if ( !$descendants )
        {
          $output .= $this->childCardBuilder( $child );
        }
      }
      $output .= "</div>";
    $output .= "</div>";

    // child pages w/ descendants
    foreach ( $children as $parent )
    {
      $descendants = get_pages( array( 'child_of' => $parent->ID ) );
      if ( $descendants )
      {
        $output .= "<div class=\"grid-section\">";
        $output .=    "<div class=\"topic-grid__title\">";
        $output .=      "<h2>";
        $output .=        $parent->post_title;
        $output .=      "</h2>";
        $output .=    "</div>";
        $output .=    "<div class='topic-grid w-children'>";
        foreach ( $descendants as $descendant )
        {
          $output .= $this->childCardBuilder( $descendant );
        }
        $output .=    "</div>";
        $output .= "</div>";
      }
    }

    return $output;
  }

  /**
   * COMPLETE PERCENTAGE BUILDER
   *
   * Builds the page's completion bar using the value provided by $this->getCompletionPercentage()
   *
   * Returns a html block containing the page's completion percentage bar
   *
   * @return string
   * @since v2022.09.1.0
   */
  public function completePercentageBuilder(): string
  {
    $completePercentage = $this->getCompletionPercentage(self::$page_object->ID);

    $output =   "<div class=\"completion-block\">";
    $output .=    "<div class='text'>";
    $output .=      "$completePercentage% Complete";
    $output .=    "</div>";
    $output .=    "<div class='full-bar'>";
    $output .=      "<div class='completion-bar' style='width: $completePercentage%'></div>";
    $output .=    "</div>";
    $output .=  "</div>";

    return $output;
  }

  /**
   * LOGO BUILDER
   *
   * Builds the logo for the header of dealer resource pages. Allows for the insertion of color into the backgrounds
   * of transparent logos.
   *
   * @return string
   * @since v2022.09.1.0
   */
  public function logoBuilder(): string
  {
    $output = "";

    $logo_url = get_field( "dealership_logo", self::$page_object->ID );
    $include_background = get_field( "dealership_logo_add_background", self::$page_object->ID );
    $background_color= htmlspecialchars(get_field( "dealership_logo_background_color", self::$page_object->ID ));

    if ( !empty($logo_url) )
    // if the page includes a logo
    {
      $output .= "<img src=\"$logo_url\" ";
      if ( $include_background )
      // if the logo should have a background
      {
        $output .= "style=\"padding: 2rem; background: $background_color\" ";
      }
      $output .= "alt=\"Dealership Logo\">";
    }

    return $output;
  }
}