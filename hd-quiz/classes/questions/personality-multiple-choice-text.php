<?php
class _hdq_question_personality_multiple_choice_text
{
    public $answers = array();
    public $weighted = false;
    public $max_answers = 10;
    public $quiz = array(); // quiz settings

    function __construct($data)
    {
        $this->weighted = $data->weighted;
        $this->max_answers = $data->max_answers;
        $this->quiz = $data->quiz;
        $this->answers = $data->data["question_answers"];
    }

    public function print()
    {
        ob_start();
        $width = 30;
        if ($this->weighted) {
            $width = 80;
            echo '<p><strong>NOTE:</strong> Weighted answers is a brand new feature. If you have issues, please let me know at the <a href = "https://hdplugins.com/forum/feedback/" target = "_blank">feedback forum</a>.</p>';
        }
?>
        <table id="hdq_answers_table" class="hdq_table hdq_answers_type_multiple_choice_image">
            <thead class="hdq_answer_row_header">
                <tr>
                    <th width="1">#</th>
                    <th>Answer<span class="hd_tooltip_item">?<span class="hd_tooltip"><span class="hd_tooltip_content">You can use basic HTML tags or even shortcodes to help with formatting.</span></span></span></th>
                    <th width="<?php echo $width; ?>" class="hdq_answer_selected">Correct</th>
                </tr>
            </thead>
            <tbody>
                <?php $this->print_answers(); ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    private function print_answers()
    {
        for ($i = 0; $i < $this->max_answers; $i++) {
            if (!isset($this->answers[$i])) {
                $this->answers[$i] = array("value" => "");
            }
        ?>
            <tr class="hdq_answer_row">
                <td>#<?php echo $i + 1; ?></td>
                <td><input type="text" class="hd_input hdq_answer_item_input" data-answer-type="value" value="<?php echo esc_attr($this->answers[$i]["value"]); ?>" placeholder="enter answer..." /></td>
                <td>
                    <div class="hdq_answer_item">
                        <?php
                        if (!$this->weighted) {
                            $this->print_checkbox($i);
                        } else {
                            $this->print_weights($i);
                        }
                        ?>
                    </div>
                </td>
            </tr>
        <?php
        }
    }

    private function print_weights($i)
    {
        foreach ($this->quiz["personality_results"] as $outcome) {
            $value = "";
            if (isset($this->answers[$i]["selected_" . $outcome["id"]])) {
                if ($this->answers[$i]["selected_" . $outcome["id"]] === "yes") {
                    $value = 1;
                } else {
                    $value = intval($this->answers[$i]["selected_" . $outcome["id"]]);
                }
            }
        ?>
            <div class="hdq_answer_item">
                <div data-type="integer" data-required="" class="hd_input_integer hdq_answer_input" data-tab="Main">
                    <div class="hd_input_row">
                        <input type="number" data-answer-type="selected_<?php echo esc_attr($outcome["id"]); ?>" data-type="integer" minlength="1" maxlength="10" data-required="" class="hderp hd_input hdq_answer_item_input" id="" value="<?php echo esc_attr($value); ?>">
                    </div>
                </div>
            </div>

        <?php
        }
    }

    private function print_checkbox($i)
    {
        foreach ($this->quiz["personality_results"] as $outcome) {
            $checked = "";
            if (isset($this->answers[$i]["selected_" . $outcome["id"]])) {
                $checked = "checked";
            }
        ?>
            <div class="hdq_answer_item">
                <div data-type="checkbox" data-required="" class="hd_input_checkbox hdq_answer_input" data-tab="Main">
                    <div class="hd_input_row">
                        <label class="hd_label_input" style="width: max-content" data-type="radio" data-id="hdq_correct_answer" for="hdq_correct_answer_<?php echo $i . '_' . esc_attr($outcome["id"]); ?>">
                            <div class="hd_options_check">
                                <input type="checkbox" title="Correct" data-answer-type="selected_<?php echo esc_attr($outcome["id"]); ?>" data-id="hdq_correct_answer" class="hd_option hd_check_input hdq_answer_item_input" data-type="radio" value="yes" name="hdq_correct_answer_<?php echo $i . '_' . esc_attr($outcome["id"]); ?>" autocomplete="off" id="hdq_correct_answer_<?php echo $i . '_' . esc_attr($outcome["id"]); ?>" <?php echo $checked; ?> />
                                <span class="hd_toggle"><span class="hd_aria_label" style="display: none">Correct answer</span></span>
                            </div>
                            <span><?php echo $outcome["label"]; ?></span>
                        </label>
                    </div>
                </div>
            </div>
<?php
        }
    }
}

// Make sure there are some results/outcomes before adding questions
if ($this->quiz["personality_results"] == "" || empty($this->quiz["personality_results"])) {
    $res = new stdClass();
    $res->status = "success";
    $res->html = '<p id = "hdq_about">' . __("Please edit your quiz settings to create your different outcomes before creating questions.") . '</p>';
    echo json_encode($res);
    return;
}


$question = new _hdq_question_personality_multiple_choice_text($this);
$html = $question->print();

$res = new stdClass();
$res->status = "success";
$res->html = $html;
$res->action = new \stdClass();
echo json_encode($res);
