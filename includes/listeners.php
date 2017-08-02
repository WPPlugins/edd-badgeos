<?php
/**
 * EDD BadgeOS Listeners
 *
 * @package EDD\BadgeOS\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_BadgeOS_Listeners' ) ) {

    class EDD_BadgeOS_Listeners {

        public function __construct() {
            // Easy Digital Downloads
            add_action( 'publish_download', array( $this, 'publish_listener' ), 0 ); // Internal BadgeOS listener
            add_action( 'edd_complete_purchase', array( $this, 'new_purchase' ) );

            // EDD FES
            if( class_exists('EDD_Front_End_Submissions') ) {
                add_action( 'fes_approve_download_admin', array( $this, 'approve_download' ), 0 ); // On approve download = publish a new download
            }

            // EDD Wish Lists
            if( class_exists('EDD_Wish_Lists') ) {
                add_action( 'publish_edd_wish_list', array( $this, 'publish_listener' ), 0 ); // Internal BadgeOS listener
                add_action( 'edd_wl_post_add_to_list', array( $this, 'add_to_wish_list' ), 0, 3 );
            }

            // EDD Downloads Lists
            if( class_exists('EDD_Downloads_Lists') ) {
                add_action( 'edd_downloads_list_add_to_list', array( $this, 'add_to_list' ), 0, 4 );
            }

            // EDD Reviews
            if( class_exists('EDD_Reviews') ) {
                add_action( 'comment_approved_edd_review', array( $this, 'approved_review_listener' ), 0, 2 );
                add_action( 'wp_insert_comment', array( $this, 'approved_review_listener' ), 0, 2 );
            }

            // EDD Download Pages
            if( class_exists('EDD_Download_Pages') ) {
                add_action( 'publish_edd_download_page', array( $this, 'publish_listener' ), 0 );
                add_action( 'edd_download_pages_fes_approve_download_page_admin', array( $this, 'approve_download_page' ), 0 ); // On approve download page = publish a new download page
            }

            // EDD Social Discounts
            if( class_exists('EDD_Social_Discounts') ) {
                add_filter( 'edd_social_discounts_ajax_return', array( $this, 'share_download' ), 0 );
            }
        }

        /**
         * Listener function for any post type publishing
         *
         * This triggers a separate hook, edd_badgeos_new_{$post_type},
         * only if the published content is brand new
         *
         * @since  1.1.0
         * @param  integer $post_id The post ID
         * @return void
         */
        function publish_listener( $post_id = 0 ) {

            // Bail if we're not intentionally saving a post
            if (
                defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE // If we're autosaving,
                || wp_is_post_revision( $post_id )            // or this is a revision
            )
                return;

            // Bail if we have more than the single, ititial revision
            $revisions = wp_get_post_revisions( $post_id );
            if ( count( $revisions ) > 1 )
                return;

            // Trigger a badgeos_new_{$post_type} action
            $post = get_post( $post_id );
            do_action( "edd_badgeos_new_{$post->post_type}", $post_id, $post->post_author, $post );

        }

        // Easy Digital Downloads
        public function new_purchase( $payment_id ) {
            $payment = new EDD_Payment( $payment_id );

            if( $payment ) {
                do_action( 'edd_badgeos_new_purchase', (int) $payment_id, (int) $payment->user_id );

                $cart_details = $payment->cart_details;

                if ( is_array( $cart_details ) ) {
                    // On purchase, triggers edd_badgeos_new_download_purchase on each download purchased
                    foreach ( $cart_details as $index => $item ) {

                        // Trigger new download purchase
                        do_action( 'edd_badgeos_new_download_purchase', (int) $item['id'], (int) $payment->user_id );

                        if( $item['item_price'] > 0 ) {
                            // Trigger new paid download purchase
                            do_action( 'edd_badgeos_new_paid_download_purchase', (int) $item['id'], (int) $payment->user_id );
                        } else {
                            // Trigger new free download purchase
                            do_action( 'edd_badgeos_new_free_download_purchase', (int) $item['id'], (int) $payment->user_id );
                        }
                    }
                }
            }
        }

        // EDD FES
        // On approve download = publish a new download
        public function approve_download( $download_id = 0 ) {
            // If do not have revisions or have only one then badgeos_publish_listener will be triggered
            $revisions = wp_get_post_revisions( $download_id );

            if ( count( $revisions ) > 1 ) {
                return;
            }

            $download = get_post( $download_id );

            do_action( 'edd_badgeos_new_download', $download_id, $download->post_author, $download );
        }

        // EDD Wish Lists
        public function add_to_wish_list( $list_id, $download_id, $options ) {
            $post = get_post( $list_id );

            do_action( 'edd_badgeos_add_to_wish_list', $list_id, $post->post_author, $post );
        }

        // EDD Downloads Lists
        public function add_to_list( $list_id, $download_id, $options, $list ) {
            $post = get_post( $list_id );

            do_action( 'edd_badgeos_' . $list . '_download', $list_id, $post->post_author, $post );
        }

        // EDD Reviews
        public function approved_review_listener( $comment_ID, $comment ) {

            // Enforce array for both hooks (wp_insert_comment uses object, comment_{status}_comment uses array)
            if ( is_object( $comment ) ) {
                $comment = get_object_vars( $comment );
            }

            // Check if comment is a review
            if ( $comment[ 'comment_type' ] != 'edd_review' ) {
                return;
            }

            // Check if comment is approved
            if ( 1 != (int) $comment[ 'comment_approved' ] ) {
                return;
            }

            // Trigger a comment actions
            do_action( 'edd_badgeos_specific_new_review', (int) $comment_ID, (int) $comment[ 'user_id' ], $comment[ 'comment_post_ID' ], $comment );
            do_action( 'edd_badgeos_new_review', (int) $comment_ID, (int) $comment[ 'user_id' ], $comment[ 'comment_post_ID' ], $comment );
        }

        // EDD Download Pages
        // On approve download page = publish a new download page
        public function approve_download_page( $download_page_id = 0 ) {
            // If do not have revisions or have only one then badgeos_publish_listener will be triggered
            $revisions = wp_get_post_revisions( $download_page_id );

            if ( count( $revisions ) > 1 ) {
                return;
            }

            $download_page = get_post( $download_page_id );

            do_action( 'edd_badgeos_new_edd_download_page', $download_page_id, $download_page->post_author, $download_page );
        }

        // EDD Social Discounts
        public function share_download( $return ) {
            // Is share is valid, then trigger share download event
            if( $return['msg'] === 'valid' ) {
                do_action( 'edd_badgeos_share_download', $return['product_id'], get_current_user_id() );
            }

            return $return;
        }

    }

}