<?php

namespace Drupal\gtranslate\Plugin\Block;

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
    $settings = \Drupal::config('gtranslate.settings');

    $gtranslate_main_lang =  $settings->get('gtranslate_main_lang');
    $gtranslate_enterprise =  $settings->get('gtranslate_enterprise');
    $gtranslate_pro =  $settings->get('gtranslate_pro');
    $gtranslate_method = 'onfly';
    $gtranslate_look =  $settings->get('gtranslate_look');
    $gtranslate_flag_size =  $settings->get('gtranslate_flag_size');
    $gtranslate_new_window =  $settings->get('gtranslate_new_window');
    $gtranslate_analytics =  $settings->get('gtranslate_analytics');
    $block_content = '';
    $languages = array('en'=>'English','ar'=>'Arabic','bg'=>'Bulgarian','zh-CN'=>'Chinese (Simplified)','zh-TW'=>'Chinese (Traditional)','hr'=>'Croatian','cs'=>'Czech','da'=>'Danish','nl'=>'Dutch','fi'=>'Finnish','fr'=>'French','de'=>'German','el'=>'Greek','hi'=>'Hindi','it'=>'Italian','ja'=>'Japanese','ko'=>'Korean','no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese','ro'=>'Romanian','ru'=>'Russian','es'=>'Spanish','sv'=>'Swedish','ca'=>'Catalan','tl'=>'Filipino','iw'=>'Hebrew','id'=>'Indonesian','lv'=>'Latvian','lt'=>'Lithuanian','sr'=>'Serbian','sk'=>'Slovak','sl'=>'Slovenian','uk'=>'Ukrainian','vi'=>'Vietnamese','sq'=>'Albanian','et'=>'Estonian','gl'=>'Galician','hu'=>'Hungarian','mt'=>'Maltese','th'=>'Thai','tr'=>'Turkish','fa'=>'Persian','af'=>'Afrikaans','ms'=>'Malay','sw'=>'Swahili','ga'=>'Irish','cy'=>'Welsh','be'=>'Belarusian','is'=>'Icelandic','mk'=>'Macedonian','yi'=>'Yiddish','hy'=>'Armenian','az'=>'Azerbaijani','eu'=>'Basque','ka'=>'Georgian','ht'=>'Haitian Creole','ur'=>'Urdu','bn' => 'Bengali','bs' => 'Bosnian','ceb' => 'Cebuano','eo' => 'Esperanto','gu' => 'Gujarati','ha' => 'Hausa','hmn' => 'Hmong','ig' => 'Igbo','jw' => 'Javanese','kn' => 'Kannada','km' => 'Khmer','lo' => 'Lao','la' => 'Latin','mi' => 'Maori','mr' => 'Marathi','mn' => 'Mongolian','ne' => 'Nepali','pa' => 'Punjabi','so' => 'Somali','ta' => 'Tamil','te' => 'Telugu','yo' => 'Yoruba','zu' => 'Zulu','my' => 'Myanmar (Burmese)','ny' => 'Chichewa','kk' => 'Kazakh','mg' => 'Malagasy','ml' => 'Malayalam','si' => 'Sinhala','st' => 'Sesotho','su' => 'Sudanese','tg' => 'Tajik','uz' => 'Uzbek','am' => 'Amharic','co' => 'Corsican','haw' => 'Hawaiian','ku' => 'Kurdish (Kurmanji)','ky' => 'Kyrgyz','lb' => 'Luxembourgish','ps' => 'Pashto','sm' => 'Samoan','gd' => 'Scottish Gaelic','sn' => 'Shona','sd' => 'Sindhi','fy' => 'Frisian','xh' => 'Xhosa');
    $flag_map = array();
    $i = $j = 0;
    foreach($languages as $lang => $lang_name) {
      $flag_map[$lang] = array($i*100, $j*100);
      if($i == 7) {
        $i = 0;
        $j++;
      } else {
        $i++;
      }
    }

    $flag_map_vertical = array();
    $i = 0;
    foreach($languages as $lang => $lang_name) {
      $flag_map_vertical[$lang] = $i*16;
      $i++;
    }

    // Move the default language to the first position and sort
    asort($languages);
    $languages = array_merge(array(
      $gtranslate_main_lang => $languages[$gtranslate_main_lang],
      'ar'=>'Arabic',
      'zh-CN'=>'Chinese (Simplified)',
      'zh-TW'=>'Chinese (Traditional)',
      'fr'=>'French',
      'ja'=>'Japanese',
      'ne' => 'Nepali',
      'es'=>'Spanish',
      'sw'=>'Swahili'
    ), $languages);

    // use redirect method if using Pro or Enterprise
    if ($gtranslate_pro or $gtranslate_enterprise) {
      $gtranslate_method = 'redirect';
    }
    else {
      $gtranslate_method = 'onfly';
    }

    if ($gtranslate_method == 'onfly') {
      $block_content = <<<EOT
      <script>eval(unescape("eval%28function%28p%2Ca%2Cc%2Ck%2Ce%2Cr%29%7Be%3Dfunction%28c%29%7Breturn%28c%3Ca%3F%27%27%3Ae%28parseInt%28c/a%29%29%29+%28%28c%3Dc%25a%29%3E35%3FString.fromCharCode%28c+29%29%3Ac.toString%2836%29%29%7D%3Bif%28%21%27%27.replace%28/%5E/%2CString%29%29%7Bwhile%28c--%29r%5Be%28c%29%5D%3Dk%5Bc%5D%7C%7Ce%28c%29%3Bk%3D%5Bfunction%28e%29%7Breturn%20r%5Be%5D%7D%5D%3Be%3Dfunction%28%29%7Breturn%27%5C%5Cw+%27%7D%3Bc%3D1%7D%3Bwhile%28c--%29if%28k%5Bc%5D%29p%3Dp.replace%28new%20RegExp%28%27%5C%5Cb%27+e%28c%29+%27%5C%5Cb%27%2C%27g%27%29%2Ck%5Bc%5D%29%3Breturn%20p%7D%28%276%207%28a%2Cb%29%7Bn%7B4%282.9%29%7B3%20c%3D2.9%28%22o%22%29%3Bc.p%28b%2Cf%2Cf%29%3Ba.q%28c%29%7Dg%7B3%20c%3D2.r%28%29%3Ba.s%28%5C%27t%5C%27+b%2Cc%29%7D%7Du%28e%29%7B%7D%7D6%20h%28a%29%7B4%28a.8%29a%3Da.8%3B4%28a%3D%3D%5C%27%5C%27%29v%3B3%20b%3Da.w%28%5C%27%7C%5C%27%29%5B1%5D%3B3%20c%3B3%20d%3D2.x%28%5C%27y%5C%27%29%3Bz%283%20i%3D0%3Bi%3Cd.5%3Bi++%294%28d%5Bi%5D.A%3D%3D%5C%27B-C-D%5C%27%29c%3Dd%5Bi%5D%3B4%282.j%28%5C%27k%5C%27%29%3D%3DE%7C%7C2.j%28%5C%27k%5C%27%29.l.5%3D%3D0%7C%7Cc.5%3D%3D0%7C%7Cc.l.5%3D%3D0%29%7BF%286%28%29%7Bh%28a%29%7D%2CG%29%7Dg%7Bc.8%3Db%3B7%28c%2C%5C%27m%5C%27%29%3B7%28c%2C%5C%27m%5C%27%29%7D%7D%27%2C43%2C43%2C%27%7C%7Cdocument%7Cvar%7Cif%7Clength%7Cfunction%7CGTranslateFireEvent%7Cvalue%7CcreateEvent%7C%7C%7C%7C%7C%7Ctrue%7Celse%7CdoGTranslate%7C%7CgetElementById%7Cgoogle_translate_element2%7CinnerHTML%7Cchange%7Ctry%7CHTMLEvents%7CinitEvent%7CdispatchEvent%7CcreateEventObject%7CfireEvent%7Con%7Ccatch%7Creturn%7Csplit%7CgetElementsByTagName%7Cselect%7Cfor%7CclassName%7Cgoog%7Cte%7Ccombo%7Cnull%7CsetTimeout%7C500%27.split%28%27%7C%27%29%2C0%2C%7B%7D%29%29"))</script>
      EOT;
      $block_content .= '<style>' . "\n";
      $block_content .= (
        "#goog-gt-tt {display:none !important;}\n
        .goog-te-banner-frame {display:none !important;}\n
        .goog-te-menu-value:hover {text-decoration:none !important;}\n
        body {top:0 !important;}\n
        #google_translate_element2 {display:none!important;}\n"
      );
      $block_content .= '</style>';

      $default_language = $gtranslate_main_lang;
      $block_content .= <<<EOT
      <div id="google_translate_element2"></div>
      <script>function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: '$default_language', autoDisplay: false}, 'google_translate_element2');}</script>
      <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>
      EOT;

      switch($gtranslate_look) {
        case 'flags_dropdown': {
          $block_content .= '<style>'."\n";
          $block_content .= "a.gtflag {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size."a.png');}\n";
          $block_content .= "a.gtflag:hover {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size.".png');}\n";
          $block_content .= '</style>';

          $i = $j = 0;
          foreach($languages as $lang => $lang_name) {
            if($settings->get('gtranslate_'.$lang) == 2) {
              list($flag_x, $flag_y) = $flag_map[$lang];
              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')" title="'.$lang_name.'" class="gtflag" style="font-size:'.$gtranslate_flag_size.'px;padding:1px 0;background-repeat:no-repeat;background-position:-'.$flag_x.'px -'.$flag_y.'px;"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="'.$gtranslate_flag_size.'" width="'.$gtranslate_flag_size.'" style="border:0;vertical-align:top;" alt="'.$lang_name.'" /></a> ';
            }

            if($i == 7) {
              $i = 0;
              $j++;
            } else {
              $i++;
            }
          }

          $block_content .= '<br><select onchange="doGTranslate(this);" id="gtranslate_selector" class="notranslate" aria-label="Website Language Selector">';
          $block_content .= '<option value="">Select Language</option>';

          $i = 0;
          foreach($languages as $lang => $lang_name) {
            if($settings->get('gtranslate_'.$lang)) {
              $flag_y = $flag_map_vertical[$lang];
              $block_content .= '<option value="'.$gtranslate_main_lang.'|'.$lang.'" style="'.($lang == $gtranslate_main_lang ? 'font-weight:bold;' : '').'background:url(\''.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/16l.png\') no-repeat scroll 0 -'.$flag_y.'px;padding-left:18px;">'.$lang_name.'</option>';
            }

            $i++;
          }

          $block_content .= '</select>';
        }; break;

        case 'flags': {
          $block_content .= '<style>'."\n";
          $block_content .= "a.gtflag {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size."a.png');}\n";
          $block_content .= "a.gtflag:hover {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size.".png');}\n";
          $block_content .= '</style>';

          $i = $j = 0;
          foreach($languages as $lang => $lang_name) {
            if($settings->get('gtranslate_'.$lang)) {
              list($flag_x, $flag_y) = $flag_map[$lang];

              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')" title="'.$lang_name.'" class="gtflag" style="font-size:'.$gtranslate_flag_size.'px;padding:1px 0;background-repeat:no-repeat;background-position:-'.$flag_x.'px -'.$flag_y.'px;"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="'.$gtranslate_flag_size.'" width="'.$gtranslate_flag_size.'" style="border:0;vertical-align:top;" alt="'.$lang_name.'" /></a> ';
            }

            if($i == 7) {
              $i = 0;
              $j++;
            } else {
              $i++;
            }
          }

        }; break;
        case 'dropdown': {
          $block_content .= '<select onchange="doGTranslate(this);" id="gtranslate_selector" class="notranslate" aria-label="Website Language Selector">';
          $block_content .= '<option value="">Select Language</option>';

          $i = 0;
          foreach($languages as $lang => $lang_name) {
            $flag_y = $flag_map_vertical[$lang];

            if($settings->get('gtranslate_'.$lang)) {
              $block_content .= '<option value="'.$gtranslate_main_lang.'|'.$lang.'" style="'.($lang == $gtranslate_main_lang ? 'font-weight:bold;' : '').'background:url(\''.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/16l.png\') no-repeat scroll 0 -'.$flag_y.'px;padding-left:18px;">'.$lang_name.'</option>';
            }

            $i++;
          }

          $block_content .= '</select>';
        }; break;
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

          $i = 0;
          foreach($languages as $lang => $lang_name) {
            $flag_y = $flag_map_vertical[$lang];

            if($settings->get('gtranslate_'.$lang)) {
              $block_content .= '<li class="lang-item" type="button" value="'.$gtranslate_main_lang.'|'.$lang.'">';
              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')">'.$lang_name.'</a>';
              $block_content .= '</li>';
            }

            $i++;
          }

          $block_content .= '</ul>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
        }; break;
        case 'dropdown_with_flags': {

          $current_language = isset($_SERVER['HTTP_X_GT_LANG']) ? $_SERVER['HTTP_X_GT_LANG'] : $gtranslate_main_lang;

          list($flag_x, $flag_y) = $flag_map[$current_language];

          $block_content .= '<div class="switcher notranslate">';
          $block_content .= '<div class="selected">';
          $block_content .= '<a href="#" onclick="return false;"><span class="gflag" style="background-position:-'.$flag_x.'px -'.$flag_y.'px"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="16" width="16" alt="'.$languages[$current_language].'" /></span>'.$languages[$current_language].'</a>';
          $block_content .= '</div>';
          $block_content .= '<div class="option">';

          foreach($languages as $lang => $lang_name) {
            list($flag_x, $flag_y) = $flag_map[$lang];
            if($settings->get('gtranslate_'.$lang) == '2') {
              $block_content .= '<a href="#" onclick="doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\');jQuery(this).parent().parent().find(\'div.selected a\').html(jQuery(this).html());return false;" title="'.$lang_name.'" class="nturl '.($current_language == $lang ? ' selected' : '').'"><span class="gflag" style="background-position:-'.$flag_x.'px -'.$flag_y.'px;"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="16" width="16" alt="'.$lang_name.'" /></span>'.$lang_name.'</a>';
            }
          }

          $block_content .= '</></div>';

          // Adding slider javascript
          $jquery_slider = true;

          // Adding slider css
          $module_url = base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files';
          $block_content .= (
            "<style>
              span.gflag {font-size:16px;padding:1px 0;background-repeat:no-repeat;background-image:url($module_url/16.png);}
              span.gflag img {border:0;margin-top:2px;}
              .switcher {font-family:Arial;font-size:10pt;text-align:left;cursor:pointer;overflow:hidden;width:163px;line-height:16px;}
              .switcher a {text-decoration:none;display:block;font-size:10pt;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;border:none;}
              .switcher a span.gflag {margin-right:3px;padding:0;display:block;float:left;}
              .switcher .selected {background:#FFFFFF url($module_url/switcher.png) repeat-x;position:relative;z-index:9999;}
              .switcher .selected a {border:1px solid #CCCCCC;background:url($module_url/arrow_down.png) 146px center no-repeat;color:#666666;padding:3px 5px;width:151px;}
              .switcher .selected a:hover {background:#F0F0F0 url($module_url/arrow_down.png) 146px center no-repeat;}
              .switcher .option {position:relative;z-index:9998;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;border-bottom:1px solid #CCCCCC;background-color:#EEEEEE;display:none;width:161px;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;}
              .switcher .option a {color:#000;padding:3px 5px;}
              .switcher .option a:hover {background:#FFC;}
              .switcher .option a.selected {background:#FFC;}
              #selected_lang_name {float: none;}
              .l_name {float: none !important;margin: 0;}
            </style>"
          );

        }; break;
        default: break;
      }

    } else {
      $block_content .= '<script>';

      if ($gtranslate_new_window) {
        $block_content .= "function openTab(url) {var form=document.createElement('form');form.method='post';form.action=url;form.target='_blank';document.body.appendChild(form);form.submit();}";
        if($gtranslate_pro) {
          if ($gtranslate_analytics) {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'GTranslate', lang, location.pathname+location.search]);var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='".$gtranslate_main_lang."';if(lang == '".$gtranslate_main_lang."')openTab(location.protocol+'//'+location.host+location.pathname.replace('/'+plang+'/', '/')+location.search);else openTab(location.protocol+'//'+location.host+'/'+lang+location.pathname.replace('/'+plang+'/', '/')+location.search);}";
          }
          else {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;var lang=lang_pair.split('|')[1];var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='".$gtranslate_main_lang."';if(lang == '".$gtranslate_main_lang."')openTab(location.protocol+'//'+location.host+location.pathname.replace('/'+plang+'/', '/')+location.search);else openTab(location.protocol+'//'+location.host+'/'+lang+location.pathname.replace('/'+plang+'/', '/')+location.search);}";
          }
        } else if ($gtranslate_enterprise) {
          if ($gtranslate_analytics) {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(typeof _gaq=='undefined')alert('Google Analytics is not installed, please turn off Analytics feature in GTranslate');else _gaq.push(['_trackEvent', 'doGTranslate', lang, location.hostname+location.pathname+location.search]);var plang=location.hostname.split('.')[0];if(plang.length !=2 && plang.toLowerCase() != 'zh-cn' && plang.toLowerCase() != 'zh-tw')plang='".$gtranslate_main_lang."';openTab(location.protocol+'//'+(lang == '".$gtranslate_main_lang."' ? '' : lang+'.')+location.hostname.replace('www.', '').replace(RegExp('^' + plang + '\\\\.'), '')+'" . \Drupal::request()->getRequestUri() . "');}";
          }
          else {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];var plang=location.hostname.split('.')[0];if(plang.length !=2 && plang.toLowerCase() != 'zh-cn' && plang.toLowerCase() != 'zh-tw')plang='".$gtranslate_main_lang."';openTab(location.protocol+'//'+(lang == '".$gtranslate_main_lang."' ? '' : lang+'.')+location.hostname.replace('www.', '').replace(RegExp('^' + plang + '\\\\.'), '')+'" . \Drupal::request()->getRequestUri() . "');}";
          }
        } else {
          $block_content .= "if(top.location!=self.location)top.location=self.location;";
          $block_content .= "window['_tipoff']=function(){};window['_tipon']=function(a){};";
          if ($gtranslate_analytics) {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|".$gtranslate_main_lang."')return;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'GTranslate', lang, location.pathname+location.search]);if(location.hostname!='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|<?php echo $mainlang; ?>')openTab(unescape(gfg('u')));else if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair!='".$gtranslate_main_lang."|".$gtranslate_main_lang."')openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href));else openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u')));}";
          }
          else {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|".$gtranslate_main_lang."')return;else if(location.hostname!='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|<?php echo $mainlang; ?>')openTab(unescape(gfg('u')));else if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair!='".$gtranslate_main_lang."|".$gtranslate_main_lang."')openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href));else openTab('http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u')));}";
          }
          $block_content .= 'function gfg(name) {name=name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");var regexS="[\\?&]"+name+"=([^&#]*)";var regex=new RegExp(regexS);var results=regex.exec(location.href);if(results==null)return "";return results[1];}';
        }
      } else {
        if ($gtranslate_pro) {
          if ($gtranslate_analytics) {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'GTranslate', lang, location.pathname+location.search]);var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='".$gtranslate_main_lang."';if(lang == '".$gtranslate_main_lang."')location.pathname=location.pathname.replace('/'+plang+'/', '/');else location.pathname='/'+lang+location.pathname.replace('/'+plang+'/', '/');}";
          }
          else {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;var lang=lang_pair.split('|')[1];var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW')plang='".$gtranslate_main_lang."';if(lang == '".$gtranslate_main_lang."')location.pathname=location.pathname.replace('/'+plang+'/', '/');else location.pathname='/'+lang+location.pathname.replace('/'+plang+'/', '/');}";
          }
        } else if ($gtranslate_enterprise) {
          if ($gtranslate_analytics) {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(typeof _gaq=='undefined')alert('Google Analytics is not installed, please turn off Analytics feature in GTranslate');else _gaq.push(['_trackEvent', 'doGTranslate', lang, location.hostname+location.pathname+location.search]);var plang=location.hostname.split('.')[0];if(plang.length !=2 && plang.toLowerCase() != 'zh-cn' && plang.toLowerCase() != 'zh-tw')plang='".$gtranslate_main_lang."';location.href=location.protocol+'//'+(lang == '".$gtranslate_main_lang."' ? '' : lang+'.')+location.hostname.replace('www.', '').replace(RegExp('^' + plang + '\\\\.'), '')+'" . \Drupal::request()->getRequestUri() . "';}";
          }
          else {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];var plang=location.hostname.split('.')[0];if(plang.length !=2 && plang.toLowerCase() != 'zh-cn' && plang.toLowerCase() != 'zh-tw')plang='".$gtranslate_main_lang."';location.href=location.protocol+'//'+(lang == '".$gtranslate_main_lang."' ? '' : lang+'.')+location.hostname.replace('www.', '').replace(RegExp('^' + plang + '\\\\.'), '')+'" . \Drupal::request()->getRequestUri() . "';}";
          }
        } else {
          $block_content .= "if(top.location!=self.location)top.location=self.location;";
          $block_content .= "window['_tipoff']=function(){};window['_tipon']=function(a){};";
          if ($gtranslate_analytics) {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|".$gtranslate_main_lang."')return;var lang=lang_pair.split('|')[1];_gaq.push(['_trackEvent', 'GTranslate', lang, location.pathname+location.search]);if(location.hostname!='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|".$gtranslate_main_lang."')location.href=unescape(gfg('u'));else if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair!='".$gtranslate_main_lang."|".$gtranslate_main_lang."')location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href);else location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u'));}";
          }
          else {
            $block_content .= "function doGTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|".$gtranslate_main_lang."')return;else if(location.hostname!='".$_SERVER['HTTP_HOST']."' && lang_pair=='".$gtranslate_main_lang."|".$gtranslate_main_lang."')location.href=unescape(gfg('u'));else if(location.hostname=='".$_SERVER['HTTP_HOST']."' && lang_pair!='".$gtranslate_main_lang."|".$gtranslate_main_lang."')location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href);else location.href='http://translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u'));}";
          }
          $block_content .= 'function gfg(name) {name=name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");var regexS="[\\?&]"+name+"=([^&#]*)";var regex=new RegExp(regexS);var results=regex.exec(location.href);if(results==null)return "";return results[1];}';
        }
      }

      $block_content .= '</script>';

      switch($gtranslate_look) {
        case 'flags_dropdown': {
          $block_content .= '<style>'."\n";
          $block_content .= "a.gtflag {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size."a.png');}\n";
          $block_content .= "a.gtflag:hover {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size.".png');}\n";
          $block_content .= '</style>';

          $i = $j = 0;
          foreach ($languages as $lang => $lang_name) {
            if ($settings->get('gtranslate_'.$lang) == 2) {
              list($flag_x, $flag_y) = $flag_map[$lang];

              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')" title="'.$lang_name.'" class="gtflag" style="font-size:'.$gtranslate_flag_size.'px;padding:1px 0;background-repeat:no-repeat;background-position:-'.$flag_x.'px -'.$flag_y.'px;"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="'.$gtranslate_flag_size.'" width="'.$gtranslate_flag_size.'" style="border:0;vertical-align:top;" alt="'.$lang_name.'" /></a> ';
            }

            if ($i == 7) {
              $i = 0;
              $j++;
            } else {
              $i++;
            }
          }

          $block_content .= '<select onchange="doGTranslate(this);" id="gtranslate_selector" class="notranslate" aria-label="Website Language Selector">';
          $block_content .= '<option value="">Select Language</option>';

          $i = 0;
          foreach($languages as $lang => $lang_name) {
              if($settings->get('gtranslate_'.$lang)) {
                  $flag_y = $flag_map_vertical[$lang];

                  $block_content .= '<option value="'.$gtranslate_main_lang.'|'.$lang.'" style="'.($lang == $gtranslate_main_lang ? 'font-weight:bold;' : '').'background:url(\''.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/16l.png\') no-repeat scroll 0 -'.$flag_y.'px;padding-left:18px;">'.$lang_name.'</option>';
              }

              $i++;
          }

          $block_content .= '</select>';
        }; break;

        case 'flags': {
          $block_content .= '<style>'."\n";
          $block_content .= "a.gtflag {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size."a.png');}\n";
          $block_content .= "a.gtflag:hover {background-image:url('".base_path().drupal_get_path('module', 'gtranslate')."/gtranslate-files/".$gtranslate_flag_size.".png');}\n";
          $block_content .= '</style>';

          $i = $j = 0;
          foreach ($languages as $lang => $lang_name) {
            if ($settings->get('gtranslate_'.$lang)) {
              list($flag_x, $flag_y) = $flag_map[$lang];

              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')" title="'.$lang_name.'" class="gtflag" style="font-size:'.$gtranslate_flag_size.'px;padding:1px 0;background-repeat:no-repeat;background-position:-'.$flag_x.'px -'.$flag_y.'px;"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="'.$gtranslate_flag_size.'" width="'.$gtranslate_flag_size.'" style="border:0;vertical-align:top;" alt="'.$lang_name.'" /></a> ';
            }

            if ($i == 7) {
              $i = 0;
              $j++;
            } else {
              $i++;
            }
          }

        }; break;
        case 'dropdown': {
          $block_content .= '<select onchange="doGTranslate(this);" id="gtranslate_selector" class="notranslate" aria-label="Website Language Selector">';
          $block_content .= '<option value="">Select Language</option>';

          $i = 0;
          foreach($languages as $lang => $lang_name) {
            $flag_y = $flag_map_vertical[$lang];

            if($settings->get('gtranslate_'.$lang)) {
              $block_content .= '<option value="'.$gtranslate_main_lang.'|'.$lang.'" style="'.($lang == $gtranslate_main_lang ? 'font-weight:bold;' : '').'background:url(\''.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/16l.png\') no-repeat scroll 0 -'.$flag_y.'px;padding-left:18px;">'.$lang_name.'</option>';
            }

            $i++;
          }

          $block_content .= '</select>';
        }; break;
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

          $i = 0;
          foreach($languages as $lang => $lang_name) {
            $flag_y = $flag_map_vertical[$lang];

            if($settings->get('gtranslate_'.$lang)) {
              $block_content .= '<li class="lang-item" type="button" value="'.$gtranslate_main_lang.'|'.$lang.'">';
              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')">'.$lang_name.'</a>';
              $block_content .= '</li>';
            }

            $i++;
          }

          $block_content .= '</ul>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
        }; break;        case 'overlay': {
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

          $i = 0;
          foreach($languages as $lang => $lang_name) {
            $flag_y = $flag_map_vertical[$lang];

            if($settings->get('gtranslate_'.$lang)) {
              $block_content .= '<li class="lang-item" type="button" value="'.$gtranslate_main_lang.'|'.$lang.'">';
              $block_content .= '<a href="javascript:doGTranslate(\''.$gtranslate_main_lang.'|'.$lang.'\')">'.$lang_name.'</a>';
              $block_content .= '</li>';
            }

            $i++;
          }

          $block_content .= '</ul>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
          $block_content .= '</div>';
        }; break;
        case 'dropdown_with_flags': {

          $current_language = isset($_SERVER['HTTP_X_GT_LANG']) ? $_SERVER['HTTP_X_GT_LANG'] : $gtranslate_main_lang;

          list($flag_x, $flag_y) = $flag_map[$current_language];

          $block_content .= '<div class="switcher notranslate">';
          $block_content .= '<div class="selected">';
          $block_content .= '<a href="#" onclick="return false;"><span class="gflag" style="background-position:-'.$flag_x.'px -'.$flag_y.'px"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="16" width="16" alt="'.$languages[$current_language].'" /></span>'.$languages[$current_language].'</a>';
          $block_content .= '</div>';
          $block_content .= '<div class="option">';

          foreach($languages as $lang => $lang_name) {
            list($flag_x, $flag_y) = $flag_map[$lang];

            if ($gtranslate_pro) {
              $href = ($gtranslate_main_lang == $lang) ? \Drupal::request()->getRequestUri() : '/' . $lang . \Drupal::request()->getRequestUri();
            } elseif ($gtranslate_enterprise) {
              $href = ($gtranslate_main_lang == $lang) ? \Drupal::request()->getRequestUri() : \Drupal::request()->getScheme() . '://' . $lang . '.' . str_replace('www.', '', \Drupal::request()->getHost() . \Drupal::request()->getRequestUri());
            }

            if($settings->get('gtranslate_'.$lang) == '2')
              $block_content .= '<a href="'.$href.'" title="'.$lang_name.'" class="nturl '.($current_language == $lang ? ' selected' : '').'"><span class="gflag" style="background-position:-'.$flag_x.'px -'.$flag_y.'px;"><img src="'.base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files/blank.png" height="16" width="16" alt="'.$lang_name.'" /></span>'.$lang_name.'</a>';
          }

          $block_content .= '</div></div>';

          // Adding slider javascript
          $jquery_slider = true;

          // Adding slider css
          $module_url = base_path().drupal_get_path('module', 'gtranslate').'/gtranslate-files';
          $block_content .= (
            "<style>
              span.gflag {font-size:16px;padding:1px 0;background-repeat:no-repeat;background-image:url($module_url/16.png);}
              span.gflag img {border:0;margin-top:2px;}
              .switcher {font-family:Arial;font-size:10pt;text-align:left;cursor:pointer;overflow:hidden;width:163px;line-height:16px;}
              .switcher a {text-decoration:none;display:block;font-size:10pt;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;border:none;}
              .switcher a span.gflag {margin-right:3px;padding:0;display:block;float:left;}
              .switcher .selected {background:#FFFFFF url($module_url/switcher.png) repeat-x;position:relative;z-index:9999;}
              .switcher .selected a {border:1px solid #CCCCCC;background:url($module_url/arrow_down.png) 146px center no-repeat;color:#666666;padding:3px 5px;width:151px;}
              .switcher .selected a:hover {background:#F0F0F0 url($module_url/arrow_down.png) 146px center no-repeat;}
              .switcher .option {position:relative;z-index:9998;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;border-bottom:1px solid #CCCCCC;background-color:#EEEEEE;display:none;width:161px;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;}
              .switcher .option a {color:#000;padding:3px 5px;}
              .switcher .option a:hover {background:#FFC;}
              .switcher .option a.selected {background:#FFC;}
              #selected_lang_name {float: none;}
              .l_name {float: none !important;margin: 0;}
            </style>"
          );

        }; break;
        default: break;
      }
    }

    $return = array(
      '#theme' => 'gtranslate',
      '#gtranslate_html' => $block_content,
      '#cache' => array('max-age' => 0),
    );

    if(isset($jquery_slider)) {
      $return['#attached']['library'][] = 'gtranslate/jquery-slider';
    }

    if($gtranslate_look == 'overlay') {
      $return['#attached']['library'][] = 'gtranslate/gtranslate-overlay';
    }

    return $return;
  }
}
