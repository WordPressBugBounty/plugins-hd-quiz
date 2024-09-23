<?php
/*
    HDQuiz Addons Page - shows available addon plugins for HDQ
*/


if (!current_user_can('edit_others_pages')) {
    die();
}

wp_enqueue_style(
    'hdq_admin_style',
    plugin_dir_url(__FILE__) . 'css/hdq_admin.css?v=' . HDQ_PLUGIN_VERSION
);

wp_enqueue_script(
    'hdq_admin_script',
    plugins_url('/js/hdq_admin.js?v=' . HDQ_PLUGIN_VERSION, __FILE__),
    array('jquery', 'jquery-ui-draggable'),
    HDQ_PLUGIN_VERSION,
    true
);

$today = date("Ymd");
update_option("hdq_new_addon", $today);
set_transient("hdq_new_addon", array("date" => $today, "isNew" => ""), WEEK_IN_SECONDS);
?>

<style>
    h1>span {
        color: #00749c;
        font-style: italic;
        display: block;
        position: relative;
        left: -0.05em;
    }

    .col-1-1 {
        grid-template-columns: minmax(10px, 1fr) minmax(10px, 1fr);
    }

    .cols {
        display: grid;
        grid-gap: 4rem;
    }

    .wrap {
        max-width: 1200px;
        margin: 0 auto;
    }

    .hdq_button_announce {
        padding: 10px 0;
        background: #397f39;
        color: #fff;
        cursor: pointer;
        text-align: center;
        border-radius: 3px;
        text-decoration: none;
        border: 1px solid transparent;
        display: block;
        opacity: 1;
        font-weight: bolder;
        font-size: 1rem;
        display: inline-block padding: 1em;
        line-height: 1;
    }

    .hdq_button_announce:hover {
        border: 1px solid #222;
        color: #fff;
    }

    #hd_announcement {
        margin-bottom: 4rem
    }

    #home_video {
        background: #4d4d4d;
        max-width: 100%;
        height: auto;
        box-shadow: 3px 5px 13px #2d2d2d5c;
        transform: translateX(-10px) perspective(1200px) rotateY(-15deg);
        transition: all ease-in-out 300ms;
    }

    #home_video:hover {
        transform: translateX(-10px) perspective(1200px) rotateY(15deg);
    }

    #home_video>img {
        width: 100%;
        height: auto;
        display: block;
    }

    @media (max-width: 800px) {
        .cols {
            grid-template-columns: 1fr;
        }
    }
</style>
<div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
    <div id="hd_announcement">
        <div class="wrap cols col-1-1">
            <div id="hero_heading">
                <div>
                    <h1>HDInvoice<span style="line-height: 1">A Hassle-free Invoicing Solution</span></h1>
                    <p>HDInvoice is an intuitive WordPress invoicing plugin for contractors, entrepreneurs, freelancers, and small businesses.</p>
                    <p>Stop wasting your time creating ugly PDF invoices, and let HDInvoice automate your way to payment.</p>
                </div>
            </div>
            <div id="sales_hero_item">
                <div id="home_video">
                    <img src="<?php echo plugins_url('/images/hdinvoice.webp', __FILE__); ?>" alt="HDInvoice">
                </div>
                <br />
                <p style="text-align:center">
                    <a href="https://hdplugins.com/sales/hdinvoice/?utm_source=hd-quiz" target="_blank" class="hdq_button_announce">Learn more</a>
                </p>
            </div>
        </div>
    </div>

    <div id="header">
        <h2 id="heading_title" style="margin-top:0">
            HD Quiz - Addons
        </h2>
    </div>

    <div id="hdq_addons">
        <?php
        // TODO! convert to ajax for faster initial page load
        // perhaps store in options table until transient expires?
        $data = wp_remote_get("https://hdplugins.com/plugins/hd-quiz/addons.txt");

        if (is_array($data)) {
            $data = $data["body"];
            $data = stripslashes(html_entity_decode($data));
            $data = json_decode($data);

            if (!empty($data)) {
                foreach ($data as $value) {
                    $title = sanitize_text_field($value->title);
                    $thumb = sanitize_text_field($value->thumb);
                    $description = wp_kses_post($value->description);
                    $url = sanitize_text_field($value->url);
                    $author = sanitize_text_field($value->author);
                    $price = sanitize_text_field($value->price);
                    $slug = sanitize_text_field($value->slug);
                    $verified = sanitize_text_field($value->verified);
                    $subscription = "";
                    if (isset($value->subscription)) {
                        $subscription = sanitize_text_field($value->subscription);
                    }
                    if ($price == 0) {
                        $price = "FREE";
                    } else {
                        $price = "$" . $price;
                    }
                    if ($subscription != "") {
                        $price = $price . ' / ' . $subscription;
                    }

        ?>
                    <div class="hdq_addon_item">
                        <div class="hdq_addon_item_image">
                            <img src="<?php echo esc_attr($thumb); ?>" alt="<?php echo esc_attr($title); ?>">
                        </div>
                        <div class="hdq_addon_content">
                            <h2>
                                <?php
                                echo esc_attr($title);
                                if ($verified == "verified") {
                                    echo '<span class = "hdq_verified hdq_tooltip hdq_tooltip_question">verified<span class="hdq_tooltip_content"><span>This plugin has either been developed by the author of HD Quiz or has been audited by the developer.</span></span></span>';
                                } ?> <span class="hdq_price"><?php echo esc_html($price); ?></span></h2>
                            <h4 class="hdq_addon_author">
                                developed by: <?php echo esc_html($author); ?>
                            </h4>

                            <?php echo apply_filters('hd_content', $description); ?>
                            <p style="text-align:right">
                                <?php
                                if ($slug != "" && $slug != null) {
                                    echo '<a class = "hdq_button" target = "_blank" href = "plugin-install.php?tab=plugin-information&amp;plugin=' . esc_attr($slug) . '">VIEW ADDON PAGE</a>';
                                } else {
                                    echo '<a href = "' . esc_attr($url) . '?utm_source=HDQuiz&utm_medium=addonsPage" target = "_blank" class = "hdq_button2 hdq_reverse">View Addon Page</a>';
                                } ?>
                            </p>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>

                <p>Unable to retrieve list of addons. I'm probably having some server issues, please check back later.</p>

        <?php
            }
        } else {
            echo '<p>Unable to retrieve list of addons. I am probably having some server issues, please check back later.</p>';
        }
        ?>


    </div>
</div>