<?php
/**
 * Template Name: Dealer Resources
 */

get_header();

/* Page Title and Class */
$page_title = get_the_title();
$page_class = strtolower( str_replace(' ', '-', $page_title) );

/* Pull in Template Class */
require_once( get_stylesheet_directory() . "/library/classes/dealer-resources.php");
$components = new dealerResources;

?>
<main id="body-container" class="dealer-resources <?php echo $page_class ?>">

  <section class="dealer-resources__banner" style="background-image: url('<?php echo get_field('image'); ?>')">
    <div class="content-wrapper">
      <div class="logo">
        <img src="http://localhost:9082/wp-content/uploads/2022/03/new_logo.png" alt="McGrath Honda Logo">
      </div>
      <div class="title">
        <h1><?php the_title() ?></h1>
      </div>
      <div class="completion">
        <?php echo $components->completePercentageBuilder(); ?>
      </div>
      <div class="description">
        <p>
          Lorem ipsum dolor sit amet, consectetur adipisci elit, sed eiusmod tempor incidunt ut labore et dolore magna
          aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut
          aliquid ex ea commodi consequatur. Quis aute iure reprehenderit in voluptate velit esse cillum dolore eu fugiat
          nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim
          id est laborum.
        </p>
      </div>
    </div>
    <div class="gradient-overlay__opaque-transparent"></div>
  </section>

  <section class="dealer-resources__topic-grid">
    <?php echo $components->topicGridBuilder(); ?>
  </section>

</main>


<?php get_footer(); ?>