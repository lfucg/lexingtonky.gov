<?php
/**
 * @file
 * Contains \Drupal\gtranslate\Form\GTranslateSettingsForm.
 */

namespace Drupal\gtranslate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Controller location for Live Weather Settings Form.
 */
class GTranslateSettingsForm extends ConfigFormBase {

  protected $languages = array('en'=>'English','ar'=>'Arabic','bg'=>'Bulgarian','zh-CN'=>'Chinese (Simplified)','zh-TW'=>'Chinese (Traditional)','hr'=>'Croatian','cs'=>'Czech','da'=>'Danish','nl'=>'Dutch','fi'=>'Finnish','fr'=>'French','de'=>'German','el'=>'Greek','hi'=>'Hindi','it'=>'Italian','ja'=>'Japanese','ko'=>'Korean','no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese','ro'=>'Romanian','ru'=>'Russian','es'=>'Spanish','sv'=>'Swedish','ca'=>'Catalan','tl'=>'Filipino','iw'=>'Hebrew','id'=>'Indonesian','lv'=>'Latvian','lt'=>'Lithuanian','sr'=>'Serbian','sk'=>'Slovak','sl'=>'Slovenian','uk'=>'Ukrainian','vi'=>'Vietnamese','sq'=>'Albanian','et'=>'Estonian','gl'=>'Galician','hu'=>'Hungarian','mt'=>'Maltese','th'=>'Thai','tr'=>'Turkish','fa'=>'Persian','af'=>'Afrikaans','ms'=>'Malay','sw'=>'Swahili','ga'=>'Irish','cy'=>'Welsh','be'=>'Belarusian','is'=>'Icelandic','mk'=>'Macedonian','yi'=>'Yiddish','hy'=>'Armenian','az'=>'Azerbaijani','eu'=>'Basque','ka'=>'Georgian','ht'=>'Haitian Creole','ur'=>'Urdu','bn' => 'Bengali','bs' => 'Bosnian','ceb' => 'Cebuano','eo' => 'Esperanto','gu' => 'Gujarati','ha' => 'Hausa','hmn' => 'Hmong','ig' => 'Igbo','jw' => 'Javanese','kn' => 'Kannada','km' => 'Khmer','lo' => 'Lao','la' => 'Latin','mi' => 'Maori','mr' => 'Marathi','mn' => 'Mongolian','ne' => 'Nepali','pa' => 'Punjabi','so' => 'Somali','ta' => 'Tamil','te' => 'Telugu','yo' => 'Yoruba','zu' => 'Zulu','my' => 'Myanmar (Burmese)','ny' => 'Chichewa','kk' => 'Kazakh','mg' => 'Malagasy','ml' => 'Malayalam','si' => 'Sinhala','st' => 'Sesotho','su' => 'Sudanese','tg' => 'Tajik','uz' => 'Uzbek','am' => 'Amharic','co' => 'Corsican','haw' => 'Hawaiian','ku' => 'Kurdish (Kurmanji)','ky' => 'Kyrgyz','lb' => 'Luxembourgish','ps' => 'Pashto','sm' => 'Samoan','gd' => 'Scottish Gaelic','sn' => 'Shona','sd' => 'Sindhi','fy' => 'Frisian','xh' => 'Xhosa');

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormId() {
    return 'gtranslate_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['gtranslate.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gtranslate.settings');

    $form['general'] = array(
      '#type' => 'details',
      '#title' => $this->t('General Configuration'),
      '#open' => true,
    );

    $form['general']['gtranslate_look'] = array(
        '#type' => 'select',
        '#title' => $this->t('Look'),
        '#default_value' => $config->get('gtranslate_look'),
        '#size' => 1,
        '#options' => array(
          'flags_dropdown' => 'Flags and dropdown',
          'flags' => 'Flags',
          'dropdown'=> 'Dropdown',
          'dropdown_with_flags' => 'Nice dropdown with flags',
          'overlay' => 'Nice lightbox screen overlay'
        ),
        '#description' => $this->t("Select the look of the module"),
        '#required' => TRUE
    );

    $form['general']['gtranslate_main_lang'] = array(
        '#type' => 'select',
        '#title' => $this->t('Main Language'),
        '#default_value' => $config->get('gtranslate_main_lang'),
        '#size' => 1,
        '#options' => $this->languages,
        '#description' => $this->t("Your sites main language"),
        '#required' => TRUE
    );

    $form['general']['gtranslate_pro'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Sub-directory URL structure'),
        '#default_value' => $config->get('gtranslate_pro'),
        '#description' => $this->t('Example: http://example.com/<b>ru</b>/. This feature is available only in paid plans: <a href="https://gtranslate.io/?xyz=1002#pricing" target="_blank">https://gtranslate.io/#pricing</a>'),
    );

    $form['general']['gtranslate_enterprise'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Sub-domain URL structure'),
        '#default_value' => $config->get('gtranslate_enterprise'),
        '#description' => $this->t('Example: http://<b>es.</b>example.com/. This feature is available only in paid plans: <a href="https://gtranslate.io/?xyz=1002#pricing" target="_blank">https://gtranslate.io/#pricing</a>'),
    );

    $form['general']['gtranslate_flag_size'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Flag Size'),
        '#default_value' => $config->get('gtranslate_flag_size'),
        '#options' => array(16 => '16px', 24 => '24px', 32 => '32px'),
        '#description' => $this->t("Select the flag size"),
        '#required' => TRUE
    );

    $form['general']['gtranslate_new_window'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Open translated page in a new window'),
        '#default_value' => $config->get('gtranslate_new_window'),
        '#description' => $this->t("The translated page will appear in a new window"),
    );

    $form['general']['gtranslate_analytics'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Analytics'),
        '#default_value' => $config->get('gtranslate_analytics'),
        '#description' => $this->t("If you have Google Analytics _gaq code on your site you can enable this which will allow you to see translation events in Google Analytics -&gt; Content -&gt; Event Tracking."),
    );

    $form['language'] = array(
      '#type' => 'details',
      '#title' => $this->t('Language Configuration'),
    );

    foreach($this->languages as $lang => $language)
        $form['language']["gtranslate_$lang"] = array(
            '#type' => 'radios',
            '#title' => $this->t("Show $language"),
            '#default_value' =>  $config->get('gtranslate_'.$lang, 0) ,
            '#options' => array(1=>'Yes', 0=>'No', 2=>'As a flag'),
            '#description' => $this->t("Show $language in the language list"),
            '#required' => TRUE
        );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     $form_value = $form_state->getValues();

    $this->config('gtranslate.settings')
      ->set('gtranslate_pro', $form_value['gtranslate_pro'])
      ->set('gtranslate_enterprise', $form_value['gtranslate_enterprise'])
      ->set('gtranslate_analytics', $form_value['gtranslate_analytics'])
      ->set('gtranslate_look', $form_value['gtranslate_look'])
      ->set('gtranslate_flag_size', $form_value['gtranslate_flag_size'])
      ->set('gtranslate_new_window', $form_value['gtranslate_new_window'])
      ->set('gtranslate_main_lang', $form_value['gtranslate_main_lang'])
      ->save();

    foreach($this->languages as $lang => $language)
        $this->config('gtranslate.settings')->set('gtranslate_'.$lang, $form_value['gtranslate_'.$lang])->save();

    parent::submitForm($form, $form_state);
  }
}
