<?php
/**
 * @file
 * Contains \Drupal\lex_custom\Form\GTranslateSettingsForm.
 */

namespace Drupal\lex_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Controller location for Live Weather Settings Form.
 */
class GTranslateSettingsForm extends ConfigFormBase {

  protected $languages = ['rw'=>'Kinyarwanda','en'=>'English','ar'=>'Arabic','bg'=>'Bulgarian','zh-CN'=>'Chinese (Simplified)','zh-TW'=>'Chinese (Traditional)','hr'=>'Croatian','cs'=>'Czech','da'=>'Danish','nl'=>'Dutch','fi'=>'Finnish','fr'=>'French','de'=>'German','el'=>'Greek','hi'=>'Hindi','it'=>'Italian','ja'=>'Japanese','ko'=>'Korean','no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese','ro'=>'Romanian','ru'=>'Russian','es'=>'Spanish','sv'=>'Swedish','ca'=>'Catalan','tl'=>'Filipino','iw'=>'Hebrew','id'=>'Indonesian','lv'=>'Latvian','lt'=>'Lithuanian','sr'=>'Serbian','sk'=>'Slovak','sl'=>'Slovenian','uk'=>'Ukrainian','vi'=>'Vietnamese','sq'=>'Albanian','et'=>'Estonian','gl'=>'Galician','hu'=>'Hungarian','mt'=>'Maltese','th'=>'Thai','tr'=>'Turkish','fa'=>'Persian','af'=>'Afrikaans','ms'=>'Malay','sw'=>'Swahili','ga'=>'Irish','cy'=>'Welsh','be'=>'Belarusian','is'=>'Icelandic','mk'=>'Macedonian','yi'=>'Yiddish','hy'=>'Armenian','az'=>'Azerbaijani','eu'=>'Basque','ka'=>'Georgian','ht'=>'Haitian Creole','ur'=>'Urdu','bn' => 'Bengali','bs' => 'Bosnian','ceb' => 'Cebuano','eo' => 'Esperanto','gu' => 'Gujarati','ha' => 'Hausa','hmn' => 'Hmong','ig' => 'Igbo','jw' => 'Javanese','kn' => 'Kannada','km' => 'Khmer','lo' => 'Lao','la' => 'Latin','mi' => 'Maori','mr' => 'Marathi','mn' => 'Mongolian','ne' => 'Nepali','pa' => 'Punjabi','so' => 'Somali','ta' => 'Tamil','te' => 'Telugu','yo' => 'Yoruba','zu' => 'Zulu','my' => 'Myanmar (Burmese)','ny' => 'Chichewa','kk' => 'Kazakh','mg' => 'Malagasy','ml' => 'Malayalam','si' => 'Sinhala','st' => 'Sesotho','su' => 'Sudanese','tg' => 'Tajik','uz' => 'Uzbek','am' => 'Amharic','co' => 'Corsican','haw' => 'Hawaiian','ku' => 'Kurdish (Kurmanji)','ky' => 'Kyrgyz','lb' => 'Luxembourgish','ps' => 'Pashto','sm' => 'Samoan','gd' => 'Scottish Gaelic','sn' => 'Shona','sd' => 'Sindhi','fy' => 'Frisian','xh' => 'Xhosa'];

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
    return ['lex_custom.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lex_custom.settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General Configuration'),
      '#open' => true,
    ];

    $form['general']['gtranslate_look'] = [
        '#type' => 'select',
        '#title' => $this->t('Look'),
        '#default_value' => $config->get('gtranslate_look'),
        '#size' => 1,
        '#options' => [
          'overlay' => 'Nice lightbox screen overlay'
        ],
        '#description' => $this->t("Select the look of the module"),
        '#required' => TRUE
        ];

    $form['general']['gtranslate_main_lang'] = [
        '#type' => 'select',
        '#title' => $this->t('Main Language'),
        '#default_value' => $config->get('gtranslate_main_lang'),
        '#size' => 1,
        '#options' => $this->languages,
        '#description' => $this->t("Your sites main language"),
        '#required' => TRUE
    ];

    $form['language'] = [
      '#type' => 'details',
      '#title' => $this->t('Language Configuration'),
    ];

    foreach($this->languages as $lang => $language)
        $form['language']["gtranslate_$lang"] = [
            '#type' => 'radios',
            '#title' => $this->t("Show $language"),
            '#default_value' =>  $config->get('gtranslate_'.$lang, 1),
            '#options' => [1=>'Yes', 0=>'No'],
            '#description' => $this->t("Show $language in the language list"),
        ];

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

    $this->config('lex_custom.settings')
      ->set('gtranslate_look', $form_value['gtranslate_look'])
      ->set('gtranslate_main_lang', $form_value['gtranslate_main_lang'])
      ->save();

    foreach($this->languages as $lang => $language)
        $this->config('lex_custom.settings')->set('gtranslate_'.$lang, $form_value['gtranslate_'.$lang])->save();

    parent::submitForm($form, $form_state);
  }
}
