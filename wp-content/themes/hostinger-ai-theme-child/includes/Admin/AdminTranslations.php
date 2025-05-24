<?php

namespace Hostinger\AiTheme\Admin;

defined( 'ABSPATH' ) || exit;

class AdminTranslations {
    public static function getValues(): array {
        return array(
                'ai_website_creation_site_couldnt_be_created' => __('<span style="display: flex; flex-direction: column; gap: 8px;"><h3 style="margin:0; font-size:16px">Site couldn’t be created</h3> <p style="margin-top:0; font-size:14px; color:#727586">That’s because of an issue on our end. Try creating your site again. </p></span>' ,'hostinger-ai-theme'),
                'ai_website_creation' => __( 'AI Website Creation', 'hostinger-ai-theme' ),
                'ai_website_creation_tell_us_about_your_site' => __('Tell us about your site', 'hostinger-ai-theme'),
                'ai_website_creation_brand_name' => __('Brand name', 'hostinger-ai-theme'),
                'ai_website_creation_website_type' => __('Website type', 'hostinger-ai-theme'),
                'ai_website_creation_other' => __('Other', 'hostinger-ai-theme'),
                'ai_website_creation_portfolio' => __('Portfolio', 'hostinger-ai-theme'),
                'ai_website_creation_business_and_services' => __('Business and services', 'hostinger-ai-theme'),
                'ai_website_creation_affiliate' => __('Affiliate marketing site', 'hostinger-ai-theme'),
                'ai_website_creation_blog' => __('Blog', 'hostinger-ai-theme'),
                'ai_website_creation_describe_your_site' => __('Describe your site', 'hostinger-ai-theme'),
                'ai_website_creation_description' => __('Description', 'hostinger-ai-theme'),
                'ai_website_creation_create' => __('Create', 'hostinger-ai-theme'),
                'ai_website_creation_back' => __('Back', 'hostinger-ai-theme'),
                'ai_website_creation_generating_colors' => __('Generating colors', 'hostinger-ai-theme'),
                'ai_website_creation_generating_structure' => __('Generating structure', 'hostinger-ai-theme'),
                'ai_website_creation_generating_content' => __('Generating content', 'hostinger-ai-theme'),
                'ai_website_creation_building_content' => __('Building content', 'hostinger-ai-theme'),
                'ai_website_creation_create_again' => __('Create again', 'hostinger-ai-theme'),
                'ai_website_creation_confirm_and_edit' => __('Confirm & edit', 'hostinger-ai-theme'),
                'ai_website_creation_give_us_a_minute' => __('Give us a minute', 'hostinger-ai-theme'),
                'ai_website_creation_enter_at_least_eleven_characters' => __('Enter at least 11 characters', 'hostinger-ai-theme'),
                'ai_website_creation_let_us_know_about_your_website' => __('Let us know more about your website to get the best result', 'hostinger-ai-theme'),
                'ai_website_creation_feel_free_to_add_more_details' => __('Great description! Feel free to add more details', 'hostinger-ai-theme'),
                'ai_website_creation_existing_content_deleted' => __('Existing content will be deleted', 'hostinger-ai-theme'),
                'ai_website_creation_existing_content_deleted_paragraph' => __('You have already generated content. Continuing will delete all previous content. Are you sure you want to proceed?', 'hostinger-ai-theme'),
                'ai_website_creation_cancel' => __('Cancel', 'hostinger-ai-theme'),
                'ai_website_creation_continue' => __('Continue', 'hostinger-ai-theme'),
                'this_field_is_required' => __('This field is required', 'hostinger-ai-theme'),
                'too_long_field' => __('This field should not be longer than 255 symbols.', 'hostinger-ai-theme'),
                'too_long_description' => __('This field should not be longer than 1024 symbols.', 'hostinger-ai-theme'),
            );
    }
}
