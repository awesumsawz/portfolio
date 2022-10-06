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

  <section class="dealer-resources__banner" style="background-image: url('<?php echo get_field('dealership_banner_image'); ?>')">
    <div class="content-wrapper">
      <div class="logo">
        <img src="<?php echo get_field( 'dealership_logo' ) ?>" alt="Dealership Logo">
      </div>
      <div class="title">
        <h1><?php the_title() ?></h1>
      </div>
      <div class="completion">
        <?php echo $components->completePercentageBuilder(); ?>
      </div>
      <?php
      if ( !empty( the_content() ) )
      {
        ?>
        <div class="description">
          <p><?php the_content() ?></p>
        </div>
        <?php
      }
      ?>

    </div>
    <div class="gradient-overlay__opaque-transparent"></div>
  </section>

  <section class="dealer-resources__topic-grid">
    <?php echo $components->topicGridBuilder(); ?>
  </section>

</main>

<?php get_footer(); ?>