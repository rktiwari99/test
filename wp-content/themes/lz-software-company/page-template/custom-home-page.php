<?php
/**
 * Template Name: Custom Home
 */

get_header(); ?>

<main id="skip-content" role="main">

	<?php do_action( 'lz_software_company_above_slider' ); ?>

	<?php if( get_theme_mod('lz_software_company_slider_hide_show') != ''){ ?>
		<section id="slider">
		  	<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel"> 
			    <?php $lz_software_company_slider_pages = array();
			      	for ( $count = 1; $count <= 4; $count++ ) {
				        $mod = intval( get_theme_mod( 'lz_software_company_slider' . $count ));
				        if ( 'page-none-selected' != $mod ) {
				          $lz_software_company_slider_pages[] = $mod;
				        }
			      	}
			      	if( !empty($lz_software_company_slider_pages) ) :
			        $args = array(
			          	'post_type' => 'page',
			          	'post__in' => $lz_software_company_slider_pages,
			          	'orderby' => 'post__in'
			        );
			        $query = new WP_Query( $args );
			        if ( $query->have_posts() ) :
			          $i = 1;
			    ?>     
			    <div class="carousel-inner" role="listbox">
			      	<?php  while ( $query->have_posts() ) : $query->the_post(); ?>
			        <div <?php if($i == 1){echo 'class="carousel-item active"';} else{ echo 'class="carousel-item"';}?>>
			          	<img src="<?php the_post_thumbnail_url('full'); ?>" alt="<?php the_title_attribute(); ?> "/>
			          	<div class="carousel-caption">
				            <div class="inner_carousel">
				              	<h2><?php esc_html(the_title());?></h2>
				              	<p><?php $lz_software_company_excerpt = get_the_excerpt(); echo esc_html( lz_software_company_string_limit_words( $lz_software_company_excerpt,15 ) ); ?></p>
				            </div>
				            <div class="read-btn">
			            		<a href="<?php esc_url(the_permalink());?>"><?php esc_html_e('READ MORE','lz-software-company'); ?><span class="screen-reader-text"><?php esc_html_e('READ MORE','lz-software-company'); ?></span></a>
					       	</div>
			          	</div>
			        </div>
			      	<?php $i++; endwhile; 
			      	wp_reset_postdata();?>
			    </div>
			    <?php else : ?>
			    <div class="no-postfound"></div>
		      		<?php endif;
			    endif;?>
		  	</div>
		  	<div class="clearfix"></div>
		</section>
	<?php }?>

	<?php do_action('lz_software_company_below_slider'); ?>

	<?php /*--- Our services ---*/ ?>
	<?php if( get_theme_mod('lz_software_company_services_title') != '' || get_theme_mod( 'lz_software_company_services_cat' )!= ''){ ?>
		<section id="our_service">
			<div class="container">
				<div class="services-head">
					<?php if( get_theme_mod('lz_software_company_services_title') != ''){ ?>
			        	<h3><?php echo esc_html(get_theme_mod('lz_software_company_services_title','')); ?></h3>
			        <?php }?>
			    </div>
				<div class="row">
					<?php $lz_software_company_catData1=  get_theme_mod('lz_software_company_services_cat'); 
					if($lz_software_company_catData1){ 
			  			$args = array(
							'post_type' => 'post',
							'category_name' => esc_html($lz_software_company_catData1 ,'lz-software-company')
				        );
		      		$lz_software_company_page_query = new WP_Query($args);?>
					<?php while( $lz_software_company_page_query->have_posts() ) : $lz_software_company_page_query->the_post(); ?>
						<div class="col-lg-3 col-md-6 services">
							<div class="services-box">
								<div class="servicesbox-img">
									<?php if(has_post_thumbnail()) { ?><a href="<?php esc_url(the_permalink());?>"><?php the_post_thumbnail(); ?></a>
									<?php } ?>
								</div>
								<div class="service-content">
							      	<h4><a href="<?php esc_url(the_permalink());?>"><?php esc_html(the_title());?></a></h4>
							      	<p><?php $lz_software_company_excerpt = get_the_excerpt(); echo esc_html( lz_software_company_string_limit_words( $lz_software_company_excerpt,12 ) ); ?></p>
						       	</div>
						    </div>	
						</div>
			  		<?php endwhile; 
			      	wp_reset_postdata();
			      	}?>
		        </div>
			</div>
		</section>
	<?php }?>

	<?php do_action('lz_software_company_below_services_section'); ?>

	<div class="container">
	  	<?php while ( have_posts() ) : the_post(); ?>
	        <?php the_content(); ?>
	    <?php endwhile; // end of the loop. ?>
	</div>
</main>

<?php get_footer(); ?>