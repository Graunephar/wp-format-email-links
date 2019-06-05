<?php
/**
 * Plugin Name: Format wp links
 * Plugin URI: graunephar.lol/wp-email-link
 * Description: This plugin removes all <> links from emails and instead insert proper <a> tags. If you like me uses a SMTP service for emails which escapes weird shit like links encapsulated in <>, this plugin is for you.
 * Version: 1.0
 * Author: Daniel Graungaard
 * Author URI: http://graunephar.lol
 **/


function action_wp_mail_failed($wp_error)
{
    return error_log(print_r($wp_error, true));
}


/**
 * Make wordpress send html emails
 *
 */
function graunephar_set_email_content_type()
{
    return "text/html";
}

add_filter('wp_mail_content_type', 'graunephar_set_email_content_type');


function graunephar_fix_links($wp_email)
{


    $message = preg_replace('(<|>)', '', $wp_email['message']); // Lets remove those crappy tags, wtf is that anyway

    $content = get_formatet_message($message);

    $wp_email['message'] = file_get_contents(__DIR__ . '/header.html') . $content . file_get_contents(__DIR__ . '/footer.html');

    return $wp_email;
}

add_filter('wp_mail', 'graunephar_fix_links');

function get_formatet_message($string)
{

    //https://gist.github.com/jermity/af85cdcaabdb36f96173
    //adapted from: https://stackoverflow.com/questions/11588542/get-all-urls-in-a-string-with-php
    $regex = '/https?\:\/\/[^\" \n]+/i';
    preg_match_all($regex, $string, $matches);


    $restoftext = preg_split($regex, $string); //Get everything which is not urls in message


    /** Now get alle urls and format them with proper html tags */
    $links = array();
    //note below that we use $matches[0], this is because we have an array of arrays
    foreach ($matches[0] as $url) {
        $s1 = substr($url, 0, strlen($url) - 1);
        $s2 = '<a href="' . $s1 . '">' . $s1 . '</a>';

        array_push($links, "$s2<br />\n"); // place all links in new array links
    }

    $formatet_message = braid_arrays($restoftext, $links); // Finally we can braid the two arrays together and get a nelw message with properly formatet links<

    $result = "";
    foreach ($formatet_message as $mes) {

        $result = $result . "<br>" . $mes;

    }

    return $result;
}


function braid_arrays($array1, $array2)
{

    $result = array();
    while (!empty($array1) || !empty($array2)) {
        if (!empty($array1)) {
            $result[] = array_shift($array1);
        }
        if (!empty($array2)) {
            $result[] = array_shift($array2);
        }
    }

    return $result;

}