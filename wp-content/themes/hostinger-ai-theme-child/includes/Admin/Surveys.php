<?php

namespace Hostinger\AiTheme\Admin;

use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Utils as Helper;

class Surveys
{
    public const AI_BUILDER_SURVEY_ID = 'ai_website_builder';
    public const AI_BUILDER_SURVEY_LOCATION = 'wordpress_ai_website_builder';
    public const AI_BUILDER_SURVEY_PRIORITY = 110;
    public const SUBMITTED_SURVEY_TRANSIENT = 'submitted_survey_transient';
    public const WEBSITE_BUILDER_TYPE = 'ai';
    public const DAY_IN_SECONDS = 86400;

    private SurveyManager $surveyManager;
    public function __construct(SurveyManager $surveyManager)
    {
        $this->surveyManager = $surveyManager;
    }

    public function init()
    {
        add_filter('hostinger_add_surveys', [$this, 'createSurveys']);
    }

    public function createSurveys($surveys)
    {
        if ($this->isAiWebsiteSurveyEnabled()) {
            $scoreQuestion          = esc_html__(
                'How would you rate your experience using our AI website builder to create your site? (Scale 1-10)',
                'hostinger-ai-theme'
            );
            $commentQuestion        = esc_html__(
                'Do you have any comments/suggestions to improve our AI tools?',
                'hostinger-ai-theme'
            );
            $aiWebsiteBuilderSurvey = SurveyManager::addSurvey(
                self::AI_BUILDER_SURVEY_ID,
                $scoreQuestion,
                $commentQuestion,
                self::AI_BUILDER_SURVEY_LOCATION,
                self::AI_BUILDER_SURVEY_PRIORITY
            );
            $surveys[]              = $aiWebsiteBuilderSurvey;
        }

        return $surveys;
    }


    public function isAiWebsiteSurveyEnabled() : bool {
        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }

        $notSubmitted            = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $notCompleted            = $this->surveyManager->isSurveyNotCompleted( self::AI_BUILDER_SURVEY_ID );
        $isClientEligible        = $this->surveyManager->isClientEligible();
        $websiteBuilderType      = get_option( 'hostinger_builder_type', '' );
        $isAiWebsiteNotGenerated = ! get_option( 'hostinger_ai_version', '' );

        if ( $websiteBuilderType !== self::WEBSITE_BUILDER_TYPE || $isAiWebsiteNotGenerated || ! $this->isWithinCreationDateLimit() ) {
            return false;
        }

        return $notSubmitted && $notCompleted && $isClientEligible;
    }

    private function isWithinCreationDateLimit() : bool {
        $oldestUser = get_users( array(
            'number' => 1,
            'orderby' => 'registered',
            'order' => 'ASC',
            'fields' => array( 'user_registered' ),
        ) );

        $oldestUserDate = isset( $oldestUser[0]->user_registered ) ? strtotime( $oldestUser[0]->user_registered ): false;

        return $oldestUserDate && ( time() - $oldestUserDate ) <= ( 7 * self::DAY_IN_SECONDS );
    }

}