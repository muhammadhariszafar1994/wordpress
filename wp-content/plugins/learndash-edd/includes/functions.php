<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Get variable product associated courses
 *
 * @param int      $download_id EDD_Download ID
 * @param int|null $price_id    Price option ID
 * @return array                If $price_id is specified it returns specific price option courses, otherwise it returns all price options courses
 */
function learndash_edd_get_variable_product_courses( $download_id, $price_id = null )
{
    $courses = get_post_meta( $download_id, '_edd_learndash_course_variable', true );

    // Backward compatibility: if empty, check for LD EDD <= v1.3.0 integration values to pull old associated courses
    if ( empty( $courses ) ) {
        $old_courses = get_post_meta( $download_id, '_edd_learndash_course', true );

        if ( ! empty( $price_id ) ) {
            if ( ! empty( $old_courses ) && is_array( $old_courses ) ) {
                $courses = $old_courses;
            } else {
                $courses = array();
            }
        } else {
            $prices = edd_get_variable_prices( $download_id );
            error_log( 'prices: '.print_r($prices,true));
            $courses = array();
            foreach( $prices as $price ) {
                $courses[ $price['index'] ] = $old_courses;
            }
        }
    } else {
        if ( ! empty( $price_id ) ) {
            $courses = $courses[ $price_id ] ?? array();
        }
    }

    /**
     * Filters the variable product associated courses.
     * 
     * @since 1.4.0
     * 
     * @param array     $courses        If $price_id is specified it returns specific price option courses, otherwise it returns all price options courses
     * @param int       $download_id    EDD_Download ID
     * @param int|null  $price_id       Optional. EDD_Download variable price ID. Default is null.
     */
    return apply_filters( 'learndash_edd_variable_product_courses', $courses, $download_id, $price_id );
}

/**
 * Get variable product associated groups
 *
 * @param int      $download_id EDD_Download ID
 * @param int|null $price_id    Price option ID
 * @return array                If $price_id is specified it returns specific price option groups, otherwise it returns all price options groups
 */
function learndash_edd_get_variable_product_groups( $download_id, $price_id = null )
{
    $groups = get_post_meta( $download_id, '_edd_learndash_group_variable', true );

    // Backward compatibility: if empty, check for LD EDD <= v1.3.0 integration values to pull old associated groups
    if ( empty( $groups ) ) {
        $old_groups = get_post_meta( $download_id, '_edd_learndash_group', true );

        if ( ! empty( $price_id ) ) {
            if ( ! empty( $old_groups ) && is_array( $old_groups ) ) {
                $groups = $old_groups;
            } else {
                $groups = array();
            }
        } else {
            $prices = edd_get_variable_prices( $download_id );
            error_log( 'prices: '.print_r($prices,true));
            $groups = array();
            foreach( $prices as $price ) {
                $groups[ $price['index'] ] = $old_groups;
            }
        }
    } else {
        if ( ! empty( $price_id ) ) {
            $groups = $groups[ $price_id ] ?? array();
        }
    }

    /**
     * Filters the variable product associated groups.
     * 
     * @since 1.4.0
     * 
     * @param array     $groups        If $price_id is specified it returns specific price option groups, otherwise it returns all price options groups
     * @param int       $download_id    EDD_Download ID
     * @param int|null  $price_id       Optional. EDD_Download variable price ID. Default is null.
     */
    return apply_filters( 'learndash_edd_variable_product_groups', $groups, $download_id, $price_id );
}
