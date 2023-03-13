<?php

namespace Drupal\lex_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GTranslate' block.
 *
 * @Block(
 *   id = "gtranslate_block",
 *   admin_label = @Translation("GTranslate"),
 *   category = @Translation("Accessibility"),
 * )
 */
class GTranslateBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = \Drupal::config('lex_custom.settings');

    $gtranslate_main_lang =  $settings->get('gtranslate_main_lang');
    $gtranslate_look =  $settings->get('gtranslate_look');
    $block_content = '';
    $languages = [
      'rw'=>'Kinyarwanda',
      'en'=>'English',
      'ar'=>'عربي',
      'bg'=>'български',
      'zh-CN'=>'中国人',
      'zh-TW'=>'中國人',
      'hr'=>'Hrvatski',
      'cs'=>'čeština',
      'da'=>'dansk',
      'nl'=>'Nederlands',
      'fi'=>'Suomalainen',
      'fr'=>'Français',
      'de'=>'Deutsch',
      'el'=>'Ελληνικά',
      'hi'=>'हिंदी',
      'it'=>'Italiano',
      'ja'=>'日本',
      'ko'=>'한국인',
      'no'=>'norsk',
      'pl'=>'Polski',
      'pt'=>'Português',
      'ro'=>'Română',
      'ru'=>'Русский',
      'es'=>'Español',
      'sv'=>'svenska',
      'ca'=>'català',
      'tl'=>'Filipino',
      'iw'=>'עִברִית',
      'id'=>'bahasa Indonesia',
      'lv'=>'latviski',
      'lt'=>'lietuvių',
      'sr'=>'Српски',
      'sk'=>'slovenský',
      'sl'=>'Slovenščina',
      'uk'=>'українська',
      'vi'=>'Tiếng Việt',
      'sq'=>'shqiptare',
      'et'=>'eesti keel',
      'gl'=>'galego',
      'hu'=>'Magyar',
      'mt'=>'Malti',
      'th'=>'แบบไทย',
      'tr'=>'Türkçe',
      'fa'=>'فارسی',
      'af'=>'Afrikaans',
      'ms'=>'Melayu',
      'sw'=>'kiswahili',
      'ga'=>'Gaeilge',
      'cy'=>'Cymraeg',
      'be'=>'беларускі',
      'is'=>'íslenskur',
      'mk'=>'македонски',
      'yi'=>'יידיש',
      'hy'=>'հայերեն',
      'az'=>'Azərbaycan',
      'eu'=>'euskara',
      'ka'=>'ქართული',
      'ht'=>'Kreyòl ayisyen',
      'ur'=>'اردو',
      'bn' => 'Bengali',
      'bs' => 'bosanski',
      'ceb' => 'Cebuano',
      'eo' => 'Esperanto',
      'gu' => 'ગુજરાતી',
      'ha' => 'Hausa',
      'hmn' => 'Hmoob',
      'ig' => 'Igbo',
      'jw' => 'basa jawa',
      'kn' => 'ಕನ್ನಡ',
      'km' => 'ខ្មែរ',
      'lo' => 'ພາສາລາວ',
      'la' => 'Latinus',
      'mi' => 'Maori',
      'mr' => 'मराठी',
      'mn' => 'Монгол',
      'ne' => 'नेपाली',
      'pa' => 'ਪੰਜਾਬੀ',
      'so' => 'Soomaali',
      'ta' => 'தமிழ்',
      'te' => 'తెలుగు',
      'yo' => 'Yoruba',
      'zu' => 'Zulu',
      'my' => 'မြန်မာ',
      'ny' => 'Chichewa',
      'kk' => 'қазақ',
      'mg' => 'Malagasy',
      'ml' => 'മലയാളം',
      'si' => 'සිංහල',
      'st' => 'Sesotho',
      'su' => 'Sudanese',
      'tg' => 'тоҷикӣ',
      'uz' => 'o&#39;zbek',
      'am' => 'አማርኛ',
      'co' => 'Corsu',
      'haw' => '&#39;Ōlelo Hawai&#39;i',
      'ku' => 'Kurdî',
      'ky' => 'Кыргызча',
      'lb' => 'lëtzebuergesch',
      'ps' => 'پښتو',
      'sm' => 'Samoa',
      'gd' => 'Gàidhlig na h-Alba',
      'sn' => 'Shona',
      'sd' => 'سنڌي',
      'fy' => 'Frisian',
      'xh' => 'isiXhosa'
    ];
    $flag_map = [];
    $i = $j = 0;
    foreach($languages as $lang => $lang_name) {
      $flag_map[$lang] = [$i*100, $j*100];
      if($i == 7) {
        $i = 0;
        $j++;
      } else {
        $i++;
      }
    }

    $flag_map_vertical = [];
    $i = 0;
    foreach($languages as $lang => $lang_name) {
      $flag_map_vertical[$lang] = $i*16;
      $i++;
    }

    // Move the default language to the first position and sort
    asort($languages);
    $languages = array_merge([
      $gtranslate_main_lang => $languages[$gtranslate_main_lang],
      'ar'=>'عربي',
      'zh-CN'=>'中国人',
      'zh-TW'=>'中國人',
      'fr'=>'Français',
      'ja'=>'日本',
      'ne' => 'नेपाली',
      'es'=>'Español',
      'sw'=>'kiswahili'
    ], $languages);

      $block_content = <<<EOT
      <script>eval(unescape("eval%28function%28p%2Ca%2Cc%2Ck%2Ce%2Cr%29%7Be%3Dfunction%28c%29%7Breturn%28c%3Ca%3F%27%27%3Ae%28parseInt%28c/a%29%29%29+%28%28c%3Dc%25a%29%3E35%3FString.fromCharCode%28c+29%29%3Ac.toString%2836%29%29%7D%3Bif%28%21%27%27.replace%28/%5E/%2CString%29%29%7Bwhile%28c--%29r%5Be%28c%29%5D%3Dk%5Bc%5D%7C%7Ce%28c%29%3Bk%3D%5Bfunction%28e%29%7Breturn%20r%5Be%5D%7D%5D%3Be%3Dfunction%28%29%7Breturn%27%5C%5Cw+%27%7D%3Bc%3D1%7D%3Bwhile%28c--%29if%28k%5Bc%5D%29p%3Dp.replace%28new%20RegExp%28%27%5C%5Cb%27+e%28c%29+%27%5C%5Cb%27%2C%27g%27%29%2Ck%5Bc%5D%29%3Breturn%20p%7D%28%276%207%28a%2Cb%29%7Bn%7B4%282.9%29%7B3%20c%3D2.9%28%22o%22%29%3Bc.p%28b%2Cf%2Cf%29%3Ba.q%28c%29%7Dg%7B3%20c%3D2.r%28%29%3Ba.s%28%5C%27t%5C%27+b%2Cc%29%7D%7Du%28e%29%7B%7D%7D6%20h%28a%29%7B4%28a.8%29a%3Da.8%3B4%28a%3D%3D%5C%27%5C%27%29v%3B3%20b%3Da.w%28%5C%27%7C%5C%27%29%5B1%5D%3B3%20c%3B3%20d%3D2.x%28%5C%27y%5C%27%29%3Bz%283%20i%3D0%3Bi%3Cd.5%3Bi++%294%28d%5Bi%5D.A%3D%3D%5C%27B-C-D%5C%27%29c%3Dd%5Bi%5D%3B4%282.j%28%5C%27k%5C%27%29%3D%3DE%7C%7C2.j%28%5C%27k%5C%27%29.l.5%3D%3D0%7C%7Cc.5%3D%3D0%7C%7Cc.l.5%3D%3D0%29%7BF%286%28%29%7Bh%28a%29%7D%2CG%29%7Dg%7Bc.8%3Db%3B7%28c%2C%5C%27m%5C%27%29%3B7%28c%2C%5C%27m%5C%27%29%7D%7D%27%2C43%2C43%2C%27%7C%7Cdocument%7Cvar%7Cif%7Clength%7Cfunction%7CGTranslateFireEvent%7Cvalue%7CcreateEvent%7C%7C%7C%7C%7C%7Ctrue%7Celse%7CdoGTranslate%7C%7CgetElementById%7Cgoogle_translate_element2%7CinnerHTML%7Cchange%7Ctry%7CHTMLEvents%7CinitEvent%7CdispatchEvent%7CcreateEventObject%7CfireEvent%7Con%7Ccatch%7Creturn%7Csplit%7CgetElementsByTagName%7Cselect%7Cfor%7CclassName%7Cgoog%7Cte%7Ccombo%7Cnull%7CsetTimeout%7C500%27.split%28%27%7C%27%29%2C0%2C%7B%7D%29%29"))</script>
      EOT;

      $default_language = $gtranslate_main_lang;
      $block_content .= <<<EOT
      <div id="google_translate_element2"></div>
      <script>function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: '$default_language', autoDisplay: false}, 'google_translate_element2');}</script>
      <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>
      EOT;

      switch($gtranslate_look) {
        case 'overlay': {
          $block_content .= '<button type="button" class="btn" data-toggle="modal" data-target="#translateModal">Translate</button>';
          $block_content .= '<div class="modal fade" id="translateModal" tabindex="-1" role="dialog" aria-labelledby="translateModalTitle" aria-hidden="true">';
          $block_content .= '<div class="modal-dialog" role="document">';
          $block_content .= '<div class="modal-content">';
          $block_content .= '<div class="modal-header">';
          $block_content .= '<button id="close-icon" type="button" class="close" data-dismiss="modal" aria-label="Close">';
          $block_content .= '<span aria-hidden="true">&times;</span>';
          $block_content .= '</button>';
          $block_content .= '</div>';
          $block_content .= '<div class="modal-body">';
          $block_content .= '<ul onchange="doGTranslate(this)" id="gtranslate_selector" class="notranslate" aria-label="Website Language Selector">';

          foreach($languages as $lang => $lang_name) {
            if($settings->get('gtranslate_'.$lang)) {
              $block_content .= '<li class="lang-item" type="button" value="'.$gtranslate_main_lang.'|'.$lang.'">';
              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')">'.$lang_name.'</a>';
              $block_content .= '</li>';
            }
          }

          $block_content .= '</ul>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
        }; break;

        default: break;
      }

    $return = [
      '#theme' => 'gtranslate',
      '#gtranslate_html' => $block_content,
      '#cache' => ['max-age' => 0],
    ];

    if($gtranslate_look == 'overlay') {
      $return['#attached']['library'][] = 'lex_custom/gtranslate-overlay';
    }

    return $return;
  }
}
