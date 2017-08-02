<?php
/**
 * EDD BadgeOS Triggers
 *
 * @package EDD\BadgeOS\Triggers
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_BadgeOS_Triggers' ) ) {

    class EDD_BadgeOS_Triggers {

        public function __construct() {
            // Register triggers
            add_filter( 'badgeos_activity_triggers', array($this, 'activity_triggers') );

            // Set user id
            add_filter( 'badgeos_trigger_get_user_id', array($this, 'trigger_get_user_id'), 10, 3);
        }

        public function activity_triggers( $triggers ) {
            // Easy Digital Downloads
            $triggers['edd_badgeos_new_download'] = __('Publish a new download', 'badgeos-edd');
            $triggers['edd_badgeos_new_purchase'] = __('Make a new purchase', 'badgeos-edd');
            $triggers['edd_badgeos_new_download_purchase'] = __('Purchase a download', 'badgeos-edd');
            $triggers['edd_badgeos_new_free_download_purchase'] = __('Purchase a free download', 'badgeos-edd');
            $triggers['edd_badgeos_new_paid_download_purchase'] = __('Purchase a paid download', 'badgeos-edd');

            // EDD FES

            // EDD Wish Lists
            if( class_exists('EDD_Wish_Lists') ) {
                $triggers['edd_badgeos_new_edd_wish_list'] = __('Create a new wish list', 'badgeos-edd');
                $triggers['edd_badgeos_add_to_wish_list'] = __('Add a download to any wish list', 'badgeos-edd');
            }

            // EDD Downloads Lists
            if( class_exists('EDD_Downloads_Lists') ) {
                $triggers['edd_badgeos_wish_download'] = __('Add a download to their wishes list', 'badgeos-edd');
                $triggers['edd_badgeos_favorite_download'] = __('Add a download to their favorites list', 'badgeos-edd');
                $triggers['edd_badgeos_like_download'] = __('Add a download to their likes list', 'badgeos-edd');
                $triggers['edd_badgeos_recommend_download'] = __('Add a download to their recommendations list', 'badgeos-edd');
            }

            // EDD Reviews
            if( class_exists('EDD_Reviews') ) {
                $triggers['edd_badgeos_new_review'] = __('Review a download', 'badgeos-edd');
                $triggers['edd_badgeos_specific_new_review'] = __( 'Review a specific download', 'badgeos-edd' );
            }

            // EDD Download Pages
            if( class_exists('EDD_Download_Pages') ) {
                $triggers['edd_badgeos_new_edd_download_page'] = __('Publish a new download page', 'badgeos-edd');
            }

            // EDD Social Discounts
            if( class_exists('EDD_Social_Discounts') ) {
                $triggers['edd_badgeos_share_download'] = __('Share a download', 'badgeos-edd');
            }

            return $triggers;
        }

        public function trigger_get_user_id( $user_id, $trigger, $args ) {
            switch ( $trigger ) {
                case 'edd_badgeos_new_download':
                case 'edd_badgeos_new_purchase':
                case 'edd_badgeos_new_download_purchase':
                case 'edd_badgeos_new_free_download_purchase':
                case 'edd_badgeos_new_paid_download_purchase':
                case 'edd_badgeos_new_edd_wish_list':
                case 'edd_badgeos_add_to_wish_list':
                case 'edd_badgeos_wish_download':
                case 'edd_badgeos_favorite_download':
                case 'edd_badgeos_like_download':
                case 'edd_badgeos_recommend_download':
                case 'edd_badgeos_new_review':
                case 'edd_badgeos_specific_new_review':
                case 'edd_badgeos_new_edd_download_page':
                case 'edd_badgeos_download_social_share':
                    $user_id = $args[1];
                    break;
                default :
                    $user_id = get_current_user_id();
                    break;
            }

            return $user_id;
        }
    }
}