<?php

/* Settings
------------------------------------------------------- */
function hdq_settings_save()
{
    hdq_validate_nonce($_POST);

    $data = $_POST["data"];
    $data = stripslashes($data);
    $data = json_decode($data, true);

    $settings = new _hdq_settings();
    $res = $settings->save($data);
    echo json_encode($res);
    die();
}
add_action("wp_ajax_hdq_settings_save", "hdq_settings_save");

/* Dashboard
------------------------------------------------------- */
function hdq_get_view_dashboard()
{
    $dashboard = new _hdq_dashboard();
    $dashboard->display();
    die();
}
add_action("wp_ajax_hdq_get_view_dashboard", "hdq_get_view_dashboard");

/* Quizzes
------------------------------------------------------- */
function hdq_get_view_quiz()
{
    $quiz_id = 0;
    if (isset($_POST["quiz_id"])) {
        $quiz_id = intval($_POST["quiz_id"]);
    }

    $paged = 1;
    if (isset($_POST["paged"])) {
        $paged = intval($_POST["paged"]);
    }

    $quiz = new _hdq_quiz($quiz_id);
    $quiz->paged = $paged;
    $quiz->display();
    die();
}
add_action("wp_ajax_hdq_get_view_quiz", "hdq_get_view_quiz");

function hdq_create_quiz()
{
    hdq_validate_nonce($_POST);

    $data = $_POST["data"];
    $data = stripslashes($data);
    $data = json_decode($data, true);

    $quiz = new _hdq_quiz();
    $res = $quiz->create($data);
    echo json_encode($res);
    die();
}
add_action("wp_ajax_hdq_create_quiz", "hdq_create_quiz");

function hdq_save_quiz()
{
    hdq_validate_nonce($_POST);

    $data = $_POST["data"];
    $data = stripslashes($data);
    $data = json_decode($data, true);

    $quiz_id = 0;
    if (isset($data["quiz_id"])) {
        $quiz_id = intval($data["quiz_id"]["value"]);
    }
    $quiz = new _hdq_quiz($quiz_id);
    $res = $quiz->save($data);
    echo json_encode($res);
    die();
}
add_action("wp_ajax_hdq_save_quiz", "hdq_save_quiz");

function hdq_delete_quiz()
{
    hdq_validate_nonce($_POST);

    $quiz_id = 0;
    if (isset($_POST["quiz_id"])) {
        $quiz_id = intval($_POST["quiz_id"]);
    }
    $quiz = new _hdq_quiz($quiz_id);
    $res = $quiz->delete();
    echo json_encode($res);
    die();
}
add_action("wp_ajax_hdq_delete_quiz", "hdq_delete_quiz");

/* Questions
------------------------------------------------------- */
function hdq_get_view_question()
{
    $quiz_id = 0;
    if (isset($_POST["quiz_id"])) {
        $quiz_id = intval($_POST["quiz_id"]);
    }
    $question_id = 0;
    if (isset($_POST["question_id"])) {
        $question_id = intval($_POST["question_id"]);
    }

    $question = new _hdq_question($quiz_id, $question_id);
    $question->display();
    die();
}
add_action("wp_ajax_hdq_get_view_question", "hdq_get_view_question");

function hdq_get_question_type()
{
    if (!hdq_user_permission()) {
        die();
    }

    $question_type = "";
    if (isset($_POST["question_type"])) {
        $question_type = sanitize_text_field($_POST["question_type"]);
    }

    $weighted = false;
    if (isset($_POST["weighted"])) {
        if ($_POST["weighted"] == "true") {
            $weighted = true;
        }
    }

    $quiz_id = 0;
    if (isset($_POST["quiz_id"])) {
        $quiz_id = intval($_POST["quiz_id"]);
    }

    $question_id = 0;
    if (isset($_POST["question_id"])) {
        $question_id = intval($_POST["question_id"]);
    }

    $question = new _hdq_question($quiz_id, $question_id, true);
    $question->getQuestionType($question_type, $weighted);
    die();
}
add_action("wp_ajax_hdq_get_question_type", "hdq_get_question_type");

function hdq_save_question()
{
    hdq_validate_nonce($_POST);

    $data = $_POST["data"];
    $data = stripcslashes($data);
    $data = json_decode($data, true);
    $quiz_id = 0;
    if (isset($data["quiz_id"])) {
        $quiz_id = intval($data["quiz_id"]["value"]);
    }

    $question_id = 0;
    if (isset($data["question_id"])) {
        $question_id = intval($data["question_id"]["value"]);
    }

    $question = new _hdq_question($quiz_id, $question_id);
    $res = $question->save($data);
    echo json_encode($res);
    die();
}
add_action("wp_ajax_hdq_save_question", "hdq_save_question");

function hdq_delete_question()
{
    hdq_validate_nonce($_POST);

    $quiz_id = 0;
    if (isset($_POST["quiz_id"])) {
        $quiz_id = intval($_POST["quiz_id"]);
    }

    $question_id = 0;
    if (isset($_POST["question_id"])) {
        $question_id = intval($_POST["question_id"]);
    }

    $question = new _hdq_question($quiz_id, $question_id);
    $res = $question->delete();
    echo json_encode($res);
    die();
}
add_action("wp_ajax_hdq_delete_question", "hdq_delete_question");


// CSV Import tool
function hdq_accept_csv()
{
    require_once dirname(__FILE__) . '/../classes/csv-import-tool.php';
    $csv = new _hdq_csv_import_tool();
    $csv->upload();
    die();
}
add_action("wp_ajax_hdq_accept_csv", "hdq_accept_csv");

function hdq_csv_import_question()
{
    require_once dirname(__FILE__) . '/../classes/csv-import-tool.php';
    $csv = new _hdq_csv_import_tool();
    $csv->import();
    die();
}
add_action("wp_ajax_hdq_csv_import_question", "hdq_csv_import_question");

function hdq_get_correct_answers()
{
    if (!isset($_POST['nonce'])) {
        wp_send_json_error(array("message" => "Missing nonce"));
        die();
    }

    if (!wp_verify_nonce($_POST['nonce'], 'hdq_quiz_nonce')) {
        wp_send_json_error(array("message" => "Invalid nonce"));
        die();
    }

    $data = $_POST["data"];
    $data = stripcslashes($data);
    $data = json_decode($data, true);

    $quiz_id = intval($data["quiz_id"]);
    $question_ids = array_map("intval", $data["question_ids"]);


    $quiz = hdq_get_quiz($quiz_id);
    if (!is_array($quiz) || empty($quiz['quiz_name'])) {
        echo json_encode(array("success" => false, "message" => "Quiz not found"));
        die();
    }

    $response = array(
        "status" => true,
        "data" => array()
    );
    foreach ($question_ids as $question_id) {
        $question = hdq_get_question($question_id, $quiz_id);
        if (!is_array($question) || empty($question['question_type'])) {
            echo json_encode(array("success" => false, "message" => "Question not found: ID " . $question_id));
            die();
        }

        $response["data"][] = array(
            "question_id" => $question_id,
            "correct_answers" => $question["question_answers"]
        );
    }
    echo json_encode($response);
    die();
}
add_action("wp_ajax_hdq_get_correct_answers", "hdq_get_correct_answers");
add_action("wp_ajax_nopriv_hdq_get_correct_answers", "hdq_get_correct_answers");
