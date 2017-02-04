<?php
/**
* The main template file.
*
* This is the most generic template file in a WordPress theme
* and one of the two required files for a theme (the other being style.css).
* It is used to display a page when nothing more specific matches a query.
* E.g., it puts together the home page when no home.php file exists.
*
* @link https://codex.wordpress.org/Template_Hierarchy
*
* @package glr
*/

get_header(); ?>

    <section class="hero">
            
           <!-- Hero Text -->
           <div class="hero-text">
               <h1>The <b>Great Lakes Roofing</b> Blog</h1>
               <hr>
               <p>See whats happening in the Great Lakes Roofing community.</p>                        
           </div><!-- end "hero-text" -->
     
           <div class="overlay"></div>

       </section><!-- end "hero"-->
           
       <div class="page-width clearfix">
           
           <aside class="blog-wrapper left">

        <?php if ( have_posts() ) : ?>

                   <?php /* Start the Loop */ ?>
                   <?php while ( have_posts() ) : the_post(); ?>

                   <?php

                       /*
             * Include the Post-Format-specific template for the content.
             * If you want to override this in a child theme, then include a file
             * called content-___.php (where ___ is the Post Format name) and that will be used instead.
             */
            get_template_part( 'template-parts/content', get_post_format() );
                   ?>

                   <?php endwhile; ?>
                                       
                   <?php glr_paging_nav(); ?>

        <?php else : ?>

                   <?php get_template_part( 'template-parts/content', 'none' ); ?>

        <?php endif; ?>
           </aside>
           
       <?php get_sidebar(); ?>

     
       </div><!-- end "page-width" -->
                                       
       <?php get_footer(); ?>