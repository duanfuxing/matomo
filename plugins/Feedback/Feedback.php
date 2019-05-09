<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model;
use Piwik\View;

/**
 *
 */
class Feedback extends \Piwik\Plugin
{

    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Controller.CoreHome.index.end'          => 'renderFeedbackPopup'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Feedback/stylesheets/feedback.less";
        $stylesheets[] = "plugins/Feedback/angularjs/ratefeature/ratefeature.directive.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Feedback/angularjs/ratefeature/ratefeature-model.service.js";
        $jsFiles[] = "plugins/Feedback/angularjs/ratefeature/ratefeature.controller.js";
        $jsFiles[] = "plugins/Feedback/angularjs/ratefeature/ratefeature.directive.js";
        $jsFiles[] = "plugins/Feedback/angularjs/feedback-popup/feedback-popup.controller.js";
        $jsFiles[] = "plugins/Feedback/angularjs/feedback-popup/feedback-popup.directive.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Feedback_ThankYou';
        $translationKeys[] = 'Feedback_RateFeatureTitle';
        $translationKeys[] = 'Feedback_RateFeatureThankYouTitle';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLike';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislike';
        $translationKeys[] = 'Feedback_SendFeedback';
        $translationKeys[] = 'Feedback_RateFeatureSendFeedbackInformation';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'General_Cancel';
    }

    public function renderFeedbackPopup(&$pageHtml)
    {
        $popupView = new View('@Feedback/feedbackPopup');
        $popupView->promptForFeedback = (int)$this->getShouldPromptForFeedback();
        $popupHtml = $popupView->render();
        $endOfBody = strpos($pageHtml, "</body>");
        $pageHtml = substr_replace($pageHtml, $popupHtml, $endOfBody, 0);
    }

    public function getShouldPromptForFeedback()
    {
        if (Piwik::isUserIsAnonymous()) {
            return false;
        }

        $login = Piwik::getCurrentUserLogin();
        $feedbackReminderKey = 'CoreHome.nextFeedbackReminder.' . Piwik::getCurrentUserLogin();
        $nextReminderDate = Option::get($feedbackReminderKey);

        // -1 = "never remind me again"
        if ($nextReminderDate === "-1") {
            return false;
        }

        if ($nextReminderDate === false) {
            $model = new Model();
            $user = $model->getUser($login);
            $nextReminderDate = Date::factory($user['date_registered'])->addDay(90)->getStartOfDay();
        } else {
            $nextReminderDate = Date::factory($nextReminderDate);
        }

        $now = Date::now()->getTimestamp();
        return $nextReminderDate->getTimestamp() <= $now;
    }

}
