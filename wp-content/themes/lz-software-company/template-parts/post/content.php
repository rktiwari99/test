<?php
/**
 * Template part for displaying posts
 *
 * @subpackage LZ Software Company
 * @since 1.0
 * @version 1.4
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class('inner-service'); ?>>
  <div class="article_content">
    <h3><?php esc_html(the_title()); ?></h3>
    <div class="metabox"> 
      <span class="entry-author"><i class="fas fa-user"></i><?php esc_html(the_author()); ?></span>
      <span class="entry-date"><i class="fas fa-calendar-alt"></i><?php echo esc_html( get_the_date()); ?></span>
      <span class="entry-comments"><i class="fas fa-comments"></i><?php comments_number( __('0 Comments','lz-software-company'), __('0 Comments','lz-software-company'), __('% Comments','lz-software-company') ); ?></span>
    </div>
    <?php if(has_post_thumbnail()) { ?>
      <?php the_post_thumbnail(); ?>  
    <?php }?>
    <p><?php the_excerpt(); ?></p>
    <div class="read-btn">
      <a href="<?php esc_url(the_permalink());?>"><?php esc_html_e('READ MORE','lz-software-company'); ?><i class="fas fa-long-arrow-alt-right"></i><span class="screen-reader-text"><?php esc_html_e('READ MORE','lz-software-company'); ?></span></a>
    </div>
    <div class="clearfix"></div> 
  </div>
</article>