<?php
$captcha_name = get_option('captcha_name', $params['id']);

$scopeID = uniqid('recaptcha--');

if (empty($captcha_name)) {
    $url_segment = url_segment();
    $captcha_name = $url_segment[0];
}
$form_id = "mw_contact_form_" . $params['id'];
if (isset($params['parent-module-id'])) {
    $form_id = $params['parent-module-id'];
}

if (!$captcha_name) {
    $captcha_name = 'captcha' . crc32($params['id']);
}

$captcha_name = str_replace(['-', '_', '/'], '', $captcha_name);

if(isset($params['captcha_parent_for_id'])) {
    $params['id'] = $params['captcha_parent_for_id'] .'-captcha';
}

$input_id = 'js-mw-google-recaptcha-v3-'.$params['id'].'-input';
if (isset($params['_confirm'])) {
    $input_id .= '-confirm';

}

if ($captcha_provider == 'google_recaptcha_v2'):
    ?>
    <script>
        if (typeof(grecaptcha) === 'undefined') {
            mw.require('https://www.google.com/recaptcha/api.js', true, 'recaptcha');
        }
    </script>
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                if (typeof(grecaptcha) !== 'undefined') {
                    try {
                        grecaptcha.render('js-mw-google-recaptcha-v2-<?php print $params['id'] ?>', {
                            'sitekey': '<?php echo get_option('recaptcha_v2_site_key', 'captcha'); ?>',
                            'action': '<?php echo $captcha_name; ?>',
                            'callback': function (response) {
                                $('#<?php print $input_id ?>').val(response);

                            },
                        });
                    }
                    catch (error) {

                    }
                }
            }, 1000);
        });

    </script>

    <div id="js-mw-google-recaptcha-v2-<?php print $params['id'] ?>"></div>
    <input name="captcha" type="hidden" value=""
           id="-input" class="mw-captcha-input"/>


<?php elseif ($captcha_provider == 'google_recaptcha_v3'): ?>
    <script type="text/javascript">
        mw.require('//www.google.com/recaptcha/api.js?render=<?php echo get_option('recaptcha_v3_site_key', 'captcha'); ?>', true, 'recaptcha');
    </script>

    <script>

        $(document).ready(function () {
            var captcha_el = $('#<?php print $scopeID; ?>');
            if(captcha_el) {
                var parent_form = mw.tools.firstParentWithTag(captcha_el[0], 'form');
                if (parent_form) {
                    parent_form.$beforepost = function () {
                        return new Promise(function (resolve){
                            try {
                                var res = grecaptcha.execute('<?php echo get_option('recaptcha_v3_site_key', 'captcha'); ?>', {
                                    action: '<?php echo $captcha_name; ?>'
                                });
                                res.then(function (token) {
                                    recaptchaV3Token = token;
                                    var recaptchaResponse = document.querySelector('#<?php print $scopeID; ?>');
                                    if(recaptchaResponse){
                                        recaptchaResponse.value = token;
                                    } else {
                                        console.log('element not found.');
                                    }
                                    resolve(token)
                                });
                            }
                            catch (error) {
                                console.warn(error);
                                resolve(false)
                            }
                        })

                    };
                }
            }
        });
    </script>
    <?php if (isset($params['_confirm'])) { ?>
        <h6><?php _e("Please confirm form submit"); ?></h6>
    <?php } ?>

    <input type="hidden" name="captcha" data-captcha-version="v3" id="<?php print $scopeID; ?>">

<?php endif; ?>
