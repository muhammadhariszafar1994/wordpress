<?php
/**
 * Plugin Name:       LearnDash Interactive Video
 * Description:       Adds interactive video capability to LearnDash lessons.
 * Version:           1.0.0
 * Author:            Your Name
 * Text Domain:       ld-interactive-video
 */

if (!defined('WPINC')) {
    die;
}

define('LD_INTERACTIVE_VIDEO_VERSION', '1.0.0');

// Activation
function activate_ld_interactive_video()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ld-interactive-video-activator.php';
    LD_Interactive_Video_Activator::activate();
}

register_activation_hook(__FILE__, 'activate_ld_interactive_video');

// Deactivation
function deactivate_ld_interactive_video()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ld-interactive-video-deactivator.php';
    LD_Interactive_Video_Deactivator::deactivate();
}

register_deactivation_hook(__FILE__, 'deactivate_ld_interactive_video');

// Core Loader
require plugin_dir_path(__FILE__) . 'includes/class-ld-interactive-video.php';
function run_ld_interactive_video()
{
    $plugin = new LD_Interactive_Video();
    $plugin->run();
}

run_ld_interactive_video();
add_action('admin_enqueue_scripts', 'enqueue_custom_media_uploader');
function enqueue_custom_media_uploader()
{
    // Load media uploader scripts only on post edit screens
    if (get_current_screen()->base === 'post') {
        wp_enqueue_media();
    }
}

wp_enqueue_script(
    'ld-interactive-video-js',              // ← this is the "handle"
    plugin_dir_url(__FILE__) . 'admin/js/ld-interactive-video.js',
    ['jquery'],                             // dependencies
    '1.0.0',                                // version
    true                                    // load in footer
);
// Admin Scripts
add_action('wp_enqueue_scripts', function () {
    // Enqueue Bootstrap & AOS (CDNs)
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css');
    wp_enqueue_style('aos', 'https://unpkg.com/aos@2.3.1/dist/aos.css');

    // Enqueue your custom modal CSS
    wp_enqueue_style(
        'custom-modal-style',
        get_stylesheet_directory_uri() . '/modalTemplate/custom.css',
        [],
        filemtime(get_stylesheet_directory() . '/modalTemplate/custom.css') // Add cache-busting
    );
});


add_filter('learndash_settings_fields', 'add_video_toggle_and_url_field', 10, 2);

// Enqueue media uploader globally for admin
add_action('admin_enqueue_scripts', function () {
    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    }
});

function add_modal_template_dropdown_to_lesson_settings($settings_fields = array(), $settings_section_key = '')
{
    if ($settings_section_key !== 'learndash-lesson-display-content-settings') {
        return $settings_fields;
    }

    $post_id = get_the_ID();
    if (!$post_id) return $settings_fields;

    $selected_template = get_post_meta($post_id, '_selected_template_file', true);
    $templates = [
        'modal-template-1.html' => 'First Modal Template',
        'modal-template-2.html' => 'Second Cards Template',
        'modal-template-3.html' => 'Third Modal Template',
    ];

    ob_start();
    ?>
    <select name="learndash-lesson-display-content-settings[selected_template_file]" style="width:100%;">
        <?php foreach ($templates as $filename => $label): ?>
            <option value="<?php echo esc_attr($filename); ?>" <?php selected($selected_template, $filename); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php

    $settings_fields['selected_template_file'] = array(
        'name' => 'selected_template_file',
        'type' => 'custom',
        'label' => 'Select Modal Template',
        'help_text' => 'Choose which template to use for this lesson.',
        'html' => ob_get_clean(),
    );

    return $settings_fields;
}

add_filter('learndash_settings_fields', 'add_modal_template_dropdown_to_lesson_settings', 10, 2);

// Save the selected template when the post is saved
add_action('save_post', function ($post_id) {
    if (isset($_POST['learndash-lesson-display-content-settings']['selected_template_file'])) {
        update_post_meta(
            $post_id,
            '_selected_template_file',
            sanitize_text_field($_POST['learndash-lesson-display-content-settings']['selected_template_file'])
        );
    }
});

// saving the selected modal template end

function add_video_toggle_and_url_field($settings_fields = array(), $settings_section_key = '')
{
    if ($settings_section_key !== 'learndash-lesson-display-content-settings') {
        return $settings_fields;
    }

    $post_id = get_the_ID();
    if (!$post_id) return $settings_fields;

    $video_url = '';
    $attachment_id = get_post_meta($post_id, '_interactive_video_attachment_id', true);
    if ($attachment_id) {
        $video_url = wp_get_attachment_url($attachment_id);
    } else {
        $video_url = get_post_meta($post_id, '_interactive_video_url', true);
    }

    $questions_array = maybe_unserialize(get_post_meta($post_id, '_interactive_video_questions', true)) ?: [];

    ob_start();
    ?>
    <div>
        <button type="button" class="button" id="upload_video_button">Add Media</button>
        <input type="hidden" id="enter_video_url" name="learndash-lesson-display-content-settings[enter_video]"
               value="<?php echo esc_attr($video_url); ?>"/>
    </div>

    <div id="video-info" style="margin-top: 10px;">
        <?php if (!empty($video_url)) : ?>
            <strong>Video URL:</strong> <?php echo esc_html($video_url); ?><br>
        <?php endif; ?>
    </div>

    <div id="add-question-section" style="margin-top: 10px; <?php echo $video_url ? '' : 'display: none;'; ?>">
        <button type="button" class="button button-secondary" id="add_question_btn">+ Add Question</button>
        <div id="question-fields" style="margin-top: 10px;"></div>
    </div>
    <?php wp_nonce_field('save_interactive_video', 'interactive_video_nonce'); ?>
    <script>
        function escapeHTML(str) {
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
        }

        jQuery(document).ready(function ($) {
            var mediaUploader;
            var videoUrl = <?php echo json_encode($video_url); ?>;
            var existingQuestions = JSON.parse(decodeURIComponent('<?php echo rawurlencode(json_encode($questions_array)); ?>'));

            var videoDuration = 0;

            // Preload duration if video exists
            if (videoUrl) {
                var videoElement = document.createElement("video");
                videoElement.src = videoUrl;
                videoElement.addEventListener("loadedmetadata", function () {
                    videoDuration = Math.floor(videoElement.duration);
                });
            }

            let questionIndex = 0;

            function renderQuestionBlock(time = '', question = '', options = []) {
                let localIndex = questionIndex++; // capture current index and increment global one

                let optionHTML = '';
                options = Array.isArray(options) && options.length ? options : [{ text: '', correct: false }];

                options.forEach((opt, index) => {
                    optionHTML += `
<div class="option-row" style="margin-top: 5px;">
    <textarea name="learndash-lesson-display-content-settings[interactive_questions][options_text][${localIndex}][]" placeholder="Option text" style="width: 60%; height: 60px;">${escapeHTML(opt.text)}</textarea>
    <label>
        <input type="checkbox" name="learndash-lesson-display-content-settings[interactive_questions][options_correct][${localIndex}][]" value="${index}" ${opt.correct ? 'checked' : ''} />
        Correct
    </label>
    <input type="number" name="learndash-lesson-display-content-settings[interactive_questions][options_skip_to][${localIndex}][]" placeholder="Skip to sec" style="width: 100px;" value="${opt.skip_to || ''}" min="0" />
    <input type="number" name="learndash-lesson-display-content-settings[interactive_questions][options_scores][${localIndex}][]" placeholder="scores" style="width: 100px;" value="${opt.scores || ''}" min="0" />
    <button type="button" class="button remove-option-btn">Remove Option</button>
</div>`;
                });

                const html = `
<div class="question-block" data-question-index="${localIndex}" style="margin-bottom: 15px; border: 1px solid #ccc; padding: 10px;">
    <div style="display: flex; gap: 20px; align-items: flex-start;">
        <div style="flex: 0 0 150px;">
            <label><strong>Time (in seconds):</strong></label><br>
            <input type="number" class="question-time" name="learndash-lesson-display-content-settings[interactive_questions][time][${localIndex}]" style="width: 100%;" min="0" value="${escapeHTML(time)}" />
        </div>
        <div style="flex: 1;">
            <label><strong>Question:</strong></label><br>
            <textarea name="learndash-lesson-display-content-settings[interactive_questions][question][${localIndex}]" style="width: 100%; height: 80px;">${escapeHTML(question)}</textarea>

            <div class="mcq-options" style="margin-top: 10px;">
                ${optionHTML}
            </div>
            <button type="button" class="button add-option-btn">+ Add Option</button>
        </div>
    </div>
    <button type="button" class="button remove-question-btn" style="margin-top:10px;">Remove</button>
</div>`;

                return html;
            }



            // Render existing questions
            if (Array.isArray(existingQuestions)) {
                existingQuestions.forEach(function (q) {
                    $("#question-fields").append(renderQuestionBlock(q.time, q.question, q.options || []));
                });

            }

            // Media uploader
            $("#upload_video_button").click(function (e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: "Choose a Video",
                    button: {
                        text: "Use this video"
                    },
                    library: {
                        type: "video"
                    },
                    multiple: false
                });

                mediaUploader.on("select", function () {
                    var attachment = mediaUploader.state().get("selection").first().toJSON();
                    var videoUrl = attachment.url;
                    $("#enter_video_url").val(videoUrl);

                    var videoElement = document.createElement("video");
                    videoElement.src = videoUrl;

                    videoElement.addEventListener("loadedmetadata", function () {
                        videoDuration = Math.floor(videoElement.duration);
                        $("#video-info").html("<strong>Video URL:</strong> " + videoUrl + "<br><strong>Duration:</strong> " + videoDuration + " seconds");
                        $("#add-question-section").show();
                    });
                });

                mediaUploader.open();
            });

            // Add new question
            $("#add_question_btn").click(function () {
                $("#question-fields").append(renderQuestionBlock());
            });

            // Remove question
            $(document).on("click", ".remove-question-btn", function () {
                $(this).closest(".question-block").remove();
            });

            // Validate time input
            $(document).on("input", ".question-time", function () {
                var totalTime = 0;
                $(".question-time").each(function () {
                    totalTime += parseInt($(this).val(), 10) || 0;
                });

                if (videoDuration > 0 && totalTime > videoDuration) {
                    alert("Total time cannot exceed the video duration of " + videoDuration + " seconds.");
                    $(this).val("");
                }
            });

            $(document).on("click", ".add-option-btn", function () {
                const questionBlock = $(this).closest('.question-block');
                const index = questionBlock.data('question-index');

                const optionsWrapper = $(this).siblings(".mcq-options");
                const optionCount = optionsWrapper.find('.option-row').length;

                optionsWrapper.append(`
        <div class="option-row" style="margin-top: 5px;">
            <textarea name="learndash-lesson-display-content-settings[interactive_questions][options_text][${index}][]" placeholder="Option text" style="width: 60%; height: 60px;"></textarea>
            <label>
                <input type="checkbox" name="learndash-lesson-display-content-settings[interactive_questions][options_correct][${index}][]" value="${optionCount}" />
                Correct
            </label>
            <input type="number" name="learndash-lesson-display-content-settings[interactive_questions][options_skip_to][${index}][]" placeholder="Skip to sec" style="width: 100px;" min="0" />
            <input type="number" name="learndash-lesson-display-content-settings[interactive_questions][options_scores][${index}][]" placeholder="scores" style="width: 100px;" value="" min="0" />
            <button type="button" class="button remove-option-btn">Remove Option</button>
        </div>
    `);
            });


// Remove option
            $(document).on("click", ".remove-option-btn", function () {
                $(this).closest(".option-row").remove();
            });
        });
    </script>
    <?php

    $settings_fields['enter_video'] = array(
        'name' => 'enter_video',
        'type' => 'custom',
        'label' => 'Interactive Video',
        'help_text' => 'Upload a video and add timed questions.',
        'html' => ob_get_clean(),
    );

    return $settings_fields;
}

add_action('save_post', 'save_interactive_video_data', 10, 3);

function save_interactive_video_data($post_id, $post, $update)
{
    if ('sfwd-lessons' !== $post->post_type) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['interactive_video_nonce']) && !wp_verify_nonce($_POST['interactive_video_nonce'], 'save_interactive_video')) {
        return;
    }

    if (empty($_POST['learndash-lesson-display-content-settings'])) {
        return;
    }

    $lesson_data = $_POST['learndash-lesson-display-content-settings'];

    // Save video URL or attachment
    if (!empty($lesson_data['enter_video'])) {
        $video_url = esc_url_raw($lesson_data['enter_video']);
        $attachment_id = attachment_url_to_postid($video_url);

        if ($attachment_id) {
            update_post_meta($post_id, '_interactive_video_attachment_id', $attachment_id);
            wp_update_post([
                'ID' => $attachment_id,
                'post_parent' => $post_id,
            ]);
            delete_post_meta($post_id, '_interactive_video_url');
        } else {
            update_post_meta($post_id, '_interactive_video_url', $video_url);
            delete_post_meta($post_id, '_interactive_video_attachment_id');
        }
    }

    // Save questions
    if (!empty($lesson_data['interactive_questions'])) {
        $questions = [];
        $times = $lesson_data['interactive_questions']['time'] ?? [];
        $questions_text = $lesson_data['interactive_questions']['question'] ?? [];
        $options_text = $lesson_data['interactive_questions']['options_text'] ?? [];
        $options_correct = $lesson_data['interactive_questions']['options_correct'] ?? [];
        $options_skip_to = $lesson_data['interactive_questions']['options_skip_to'] ?? [];
        $options_scores = $lesson_data['interactive_questions']['options_scores'] ?? [];

        foreach ($times as $index => $time) {
            $time = intval($time);
            $question = wp_kses_post($questions_text[$index] ?? '');
            $options = [];

            $texts = $options_text[$index] ?? [];
            $correct_indices = $options_correct[$index] ?? [];
            $skip_to_times = $options_skip_to[$index] ?? [];
            $scores = $options_scores[$index] ?? [];

            foreach ($texts as $opt_index => $opt_text_raw) {
                $opt_text = wp_kses_post($opt_text_raw);
                $is_correct = is_array($correct_indices) && in_array($opt_index, $correct_indices);
                $skip_to = isset($skip_to_times[$opt_index]) ? intval($skip_to_times[$opt_index]) : null;
                $moduleScores = isset($scores[$opt_index]) ? intval($scores[$opt_index]) : null;

                $options[] = [
                    'text' => $opt_text,
                    'correct' => $is_correct,
                    'skip_to' => $skip_to,
                    'scores' => $moduleScores,
                ];
            }

            if (!empty($question)) {
                $questions[] = [
                    'time' => $time,
                    'question' => $question,
                    'options' => $options,
                ];
            }
        }


        update_post_meta($post_id, '_interactive_video_questions', maybe_serialize($questions));
    } else {
        delete_post_meta($post_id, '_interactive_video_questions');
    }
}

add_action('wp_footer', 'load_interactive_video_data', 10);

function load_interactive_video_data()
{
    if (!is_singular('sfwd-lessons')) return;

    global $post;
    if (empty($post)) return;

    $post_id = $post->ID;
    $attachment_id = get_post_meta($post_id, '_interactive_video_attachment_id', true);
    $video_url = $attachment_id ? wp_get_attachment_url($attachment_id) : get_post_meta($post_id, '_interactive_video_url', true);
    $questions = maybe_unserialize(get_post_meta($post_id, '_interactive_video_questions', true));
    $modal_url = home_url('/wp-content/themes/ecademy-child/modalTemplate/modal-template-1.html');

    if ($video_url && !empty($questions) && is_array($questions)) {
        $nonce = wp_create_nonce('save_answer_nonce');
        $user_id = get_current_user_id();
        $user_answers = $user_id ? get_post_meta($post_id, '_user_answers_' . $user_id, true) : [];
        if (!is_array($user_answers)) $user_answers = [];

        $user_answers_js = json_encode((object)$user_answers);
        ?>
        <style>
            .interactive-video-wrapper {
                position: relative;
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
                text-align: center;
            }

            #modal-trigger-btn {
                margin-top: 20px;
            }

            video::-webkit-media-controls,
            video::-moz-media-controls,
            video::-ms-media-controls {
                display: none !important;
            }

            .interactive-options label {
                cursor: pointer;
            }

            #video-result-message {
                transition: all 0.3s ease;
                padding: 10px 15px;
                border-radius: 8px;
                margin-top: 20px;
                font-size: 1.2em;
                font-weight: bold;
            }
        </style>

        <script>
            jQuery(document).ready(function ($) {
                const videoUrl = <?php echo json_encode($video_url); ?>;
                const questions = <?php echo json_encode($questions); ?>;
                const lessonId = <?php echo json_encode($post_id); ?>;
                const ajaxNonce = <?php echo json_encode($nonce); ?>;
                const answeredQuestions = <?php echo $user_answers_js; ?>;
                const modalUrl = <?php echo json_encode($modal_url); ?>;
                let videoEnded = false;

                const wrapper = document.createElement("div");
                wrapper.className = "interactive-video-wrapper";

                const videoElement = document.createElement("video");
                videoElement.src = videoUrl;
                videoElement.controls = false; // ✅ Enable native controls
                videoElement.style.width = "100%";
                videoElement.setAttribute("playsinline", "");
                videoElement.setAttribute("preload", "metadata");

                const playBtn = document.createElement("button");
                playBtn.id = "modal-trigger-btn";
                playBtn.className = "btn btn-primary";
                playBtn.textContent = "Launch Interactive Video";

                const retakeBtn = document.createElement("button");
                retakeBtn.id = "retake-module-btn";
                retakeBtn.className = "btn btn-primary";
                retakeBtn.textContent = "Retake Module";
                retakeBtn.addEventListener("click", function () {
                    if (confirm("Are you sure you want to retake the module?")) {
                        resetModule();
                    }
                });
                const resultMessage = document.createElement("div");
                resultMessage.id = "video-result-message";


                const $target = $(".ld-tabs-content .ld-tab-content.ld-visible");
                if ($target.length) {
                    $target.prepend(wrapper);
                }
                let currentQuestion = 0;
                let score = 0;
                let passedModule = false;
                let showResultAtEnd = false;

                const maxPoints = questions.reduce((sum, q) => {
                    if (!Array.isArray(q.options)) return sum;
                    const maxScore = Math.max(...q.options.map(o => parseInt(o.scores || 0)));
                    return sum + maxScore;
                }, 0);
// ✅ Now safe to use maxPoints
                let answeredCount = Object.keys(answeredQuestions).length;
                let totalQuestions = questions.length;
                let allAnswered = answeredCount === totalQuestions;

// Calculate score if already answered
                score = 0;
                questions.forEach((q, i) => {
                    if (answeredQuestions.hasOwnProperty(i) && Array.isArray(q.options)) {
                        const answer = answeredQuestions[i];
                        const found = q.options.find(opt => opt.text === answer || opt === answer);
                        if (found) {
                            score += parseInt(found.scores || 0);
                        }
                    }
                });

                let percent = maxPoints > 0 ? (score / maxPoints) * 100 : 0;
                passedModule = percent >= 75;
                
// Add video
                wrapper.appendChild(videoElement);

// Show buttons + results
                if (allAnswered) {
                    retakeBtn.className = "btn btn-primary";
                    wrapper.appendChild(retakeBtn);

                    resultMessage.innerHTML = `
        <p><strong>Score:</strong> ${score}/${maxPoints}</p>
        <p><strong>Percentage:</strong> ${percent.toFixed(2)}%</p>
        <p style="color: ${passedModule ? 'green' : 'red'}">
            ${passedModule
                        ? '✅ You’ve passed this module! Great job!'
                        : '❌ You didn’t pass this time. You can retake the module.'}
        </p>
    `;
                    wrapper.appendChild(resultMessage);
                    // Hide/show retake button depending on fullscreen state
                    function handleFullscreenChange() {
                        const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
                        retakeBtn.style.display = isFullscreen ? "none" : "inline-block";
                    }

                    document.addEventListener("fullscreenchange", handleFullscreenChange);
                    document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
                    document.addEventListener("msfullscreenchange", handleFullscreenChange);

                    // Run it once initially
                    handleFullscreenChange();

                } else {
                    wrapper.appendChild(playBtn);
                    // wrapper.appendChild(retakeBtn);
                    wrapper.appendChild(resultMessage);
                }

                function parseTime(t) {
                    if (!t) return 0;
                    if (typeof t === "string" && t.includes(":")) {
                        const parts = t.split(":").map(Number);
                        if (parts.length === 2) {
                            return parts[0] * 60 + parts[1];
                        }
                    }
                    return parseFloat(t) || 0;
                }


                function goToNextUnanswered(startIndex = 0) {
                    for (let i = startIndex; i < questions.length; i++) {
                        if (!(i in answeredQuestions)) return i;
                    }
                    return questions.length;
                }

                function insertVideo() {
                    const $target = $(".ld-tabs-content .ld-tab-content.ld-visible");
                    if ($target.length) {
                        $target.prepend(wrapper);

                        if (!document.getElementById("step-modal")) {
                            $.get(modalUrl, function (data) {
                                wrapper.insertAdjacentHTML("beforeend", data);
                            });
                        }

                        videoElement.addEventListener("play", function () {
                            if (wrapper.requestFullscreen) wrapper.requestFullscreen();
                            else if (wrapper.webkitRequestFullscreen) wrapper.webkitRequestFullscreen();
                            else if (wrapper.msRequestFullscreen) wrapper.msRequestFullscreen();
                        });

                        videoElement.addEventListener("ended", function () {
                            videoEnded = true;
                            exitFullscreen();

                            if (showResultAtEnd) {
                                const message = passedModule
                                    ? "✅ You’ve passed this module! You’ve demonstrated key leadership behaviors and reflective insight."
                                    : "❌ It looks like you didn’t pass this time. That’s okay — this is about growth, not perfection. Take a moment to review your decisions and reflections. Then try again when you’re ready.";

                                resultMessage.innerHTML = message;
                                resultMessage.style.color = passedModule ? "green" : "red";
                            }
                        });

                        videoElement.addEventListener("timeupdate", function () {
                            if (currentQuestion >= questions.length) return;

                            const currentTime = Math.floor(videoElement.currentTime);
                            const questionTime = parseTime(questions[currentQuestion].time);

                            if (Math.abs(currentTime - questionTime) <= 1) {
                                const currentQ = questions[currentQuestion];
                                if (currentQ && currentQ.question) {
                                    videoElement.pause();
                                    showQuestion(currentQuestion);
                                }
                            }
                        });

                        // $(document).on('click', '#modal-trigger-btn', function () {
                        //     videoElement.play();
                        // });
                        $(document).on('click', '#modal-trigger-btn', function () {
                            $('#intro-launch-modal').modal('show');
                        });

                        // Add this handler for the last slide's "Start Video" button inside your modal
                        $(document).on('click', '#start-video-btn', function () {
                            $('#intro-launch-modal').modal('hide');
                            setTimeout(() => {
                                if (wrapper.requestFullscreen) wrapper.requestFullscreen();
                                else if (wrapper.webkitRequestFullscreen) wrapper.webkitRequestFullscreen();
                                else if (wrapper.msRequestFullscreen) wrapper.msRequestFullscreen();
                                videoElement.play();
                            }, 300);
                        });

// Inject new modal from a separate file (start-slide.html)
                        const introModalUrl = '<?php echo home_url('/wp-content/themes/ecademy-child/modalTemplate/start-slide.html'); ?>';
                        if (!document.getElementById("intro-launch-modal")) {
                            $.get(introModalUrl, function (html) {
                                $('body').append(html);
                            });
                        }

                        function handleFullscreenChange() {
                            const isFS = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
                            playBtn.style.display = isFS ? "none" : "inline-block";

                            if (!isFS && videoEnded) {
                                location.reload(); // ✅ Reloads when fullscreen exits after video end
                            }
                            if (!isFS && !videoElement.paused) {
                                videoElement.pause();
                            }
                        }

                        document.addEventListener("fullscreenchange", handleFullscreenChange);
                        document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
                        document.addEventListener("msfullscreenchange", handleFullscreenChange);

                    } else {
                        setTimeout(insertVideo, 300);
                    }
                }

                function exitFullscreen() {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    else if (document.msExitFullscreen) document.msExitFullscreen();
                }

                function showQuestion(index) {
                    if (index >= questions.length) return;

                    const currentQ = questions[index];
                    $('#question-text').text(currentQ.question || '');
                    const optionsContainer = $('.interactive-options');
                    optionsContainer.empty();

                    if (Array.isArray(currentQ.options)) {
                        currentQ.options.forEach((opt, i) => {
                            const value = typeof opt === 'object' ? opt.text : opt;
                            const scores = typeof opt === 'object' ? parseInt(opt.scores || 0) : 0;
                            const skipTo = typeof opt === 'object' && opt.skip_to ? opt.skip_to : '';

                            const cardHtml = `
                            <div class="option-card animated-item">
                                <label class="w-100 m-0 d-block">
                                    <input type="radio" name="answer" value="${value}" data-scores="${scores}" data-skip-to="${skipTo}" class="mr-2" />

                                    <h3 class="d-inline">Option ${i + 1}</h3>
                                    <p class="mt-2">${value}</p>
                                </label>
                            </div>`;
                            optionsContainer.append(cardHtml);
                        });
                    } else {
                        optionsContainer.append('<input type="text" id="user-answer-text" placeholder="Your answer..." class="form-control" />');
                    }

                    $('#step-modal').modal('show');
                }

                $(document).on('click', '#submit-answer', function () {
                    const $btn = $(this);
                    $btn.addClass('clicked').prop('disabled', true);

                    let answer, skipTo = null, scores = 0;

                    if ($('.interactive-options input[type="radio"]').length) {
                        const selected = $('.interactive-options input[type="radio"]:checked');
                        answer = selected.val();
                        scores = parseInt(selected.data('scores') || 0);
                        skipTo = selected.data('skip-to') || null;

                        if (!answer) {
                            alert("Please select an answer.");
                            $btn.removeClass('clicked').prop('disabled', false);
                            return;
                        }
                    } else {
                        answer = $('#user-answer-text').val().trim();
                        scores = answer ? 2 : 0;
                        if (!answer) {
                            alert("Please enter an answer.");
                            $btn.removeClass('clicked').prop('disabled', false);
                            return;
                        }
                    }

                    score += scores;

                    const currentQ = questions[currentQuestion];
                    let totalOptionScore = 0;
                    if (Array.isArray(currentQ.options)) {
                        currentQ.options.forEach(opt => {
                            totalOptionScore += parseInt(opt.scores || 0);
                        });
                    }

                    let questionPercent = totalOptionScore > 0 ? (scores / totalOptionScore) * 100 : 0;
                    let overallPercent = maxPoints > 0 ? (score / maxPoints) * 100 : 0;
                    let delta = overallPercent - questionPercent;

                    console.log(`Question ${currentQuestion + 1} selected option score: ${scores}`);
                    console.log(`Total option score for this question: ${totalOptionScore}`);
                    console.log(`Question %: ${questionPercent.toFixed(2)}%`);
                    console.log(`Overall % so far: ${overallPercent.toFixed(2)}%`);
                    console.log(`Delta (Overall - Question): ${delta.toFixed(2)}%`);

                    console.log("⏩ Skip to (raw):", skipTo);
                    console.log("⏩ Parsed time (sec):", parseTime(skipTo));
                    console.log({
                        action: 'save_answer',
                        nonce: ajaxNonce,
                        question_id: currentQuestion,
                        answer: answer,
                        lesson_id: lessonId
                    });
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'save_answer',
                            nonce: ajaxNonce,
                            question_id: currentQuestion,
                            answer: answer,
                            lesson_id: lessonId
                        },
                        success: function (res) {
                            if (res.success) {
                                $('#step-modal').modal('hide');
                                answeredQuestions[currentQuestion] = answer;
                                currentQuestion = goToNextUnanswered(currentQuestion + 1);

                                // 🔁 Smart skipping
                                if (skipTo !== null && skipTo !== "") {
                                    const targetTime = parseTime(skipTo);

                                    videoElement.pause();

                                    setTimeout(function () {
                                        try {
                                            videoElement.currentTime = targetTime;
                                            videoElement.play();
                                        } catch (err) {
                                            console.warn("❗ Could not seek:", err);
                                            videoElement.play();
                                        }
                                    }, 300);
                                } else {
                                    videoElement.play();
                                }

                                if (currentQuestion >= questions.length) {
                                    const percent = (score / maxPoints) * 100;
                                    passedModule = percent >= 75;
                                    showResultAtEnd = true;
                                }
                            } else {
                                alert('There was an error saving your answer.');
                            }

                            $btn.removeClass('clicked').prop('disabled', false);
                        },
                        error: function () {
                            $btn.removeClass('clicked').prop('disabled', false);
                        }
                    });
                });


                insertVideo();
                currentQuestion = goToNextUnanswered();
                videoElement.currentTime = 0;
                function resetModule() {
                    currentQuestion = 0;
                    score = 0;
                    passedModule = false;
                    showResultAtEnd = false;
                    resultMessage.innerHTML = ""; // ✅ now resultMessage is in scope

                    for (let key in answeredQuestions) {
                        delete answeredQuestions[key];
                    }

                    videoElement.currentTime = 0;

                    $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'reset_answers',
                        nonce: ajaxNonce,
                        lesson_id: lessonId
                    });

                    videoElement.play();
                }

                $(document).on('click', '.option-card', function (e) {
                    const $card = $(this);
                    const $radio = $card.find('input[type="radio"]');

                    if ($radio.length) {
                        // Uncheck all radios and remove active class
                        $('.option-card').removeClass('active-option');
                        $('.option-card input[type="radio"]').prop('checked', false);

                        // Check clicked radio and add active class
                        $radio.prop('checked', true);
                        $card.addClass('active-option');
                    }
                });
            });

        </script>
        <?php
    }
}


add_action('wp_ajax_save_answer', 'save_answer');

function save_answer()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'save_answer_nonce')) {
        wp_send_json_error('Nonce check failed');
        return;
    }

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }

    $user_id = get_current_user_id();
    $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
    $answer = isset($_POST['answer']) ? sanitize_textarea_field($_POST['answer']) : '';
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;

    if (!$lesson_id || $answer === '' || $question_id < 0) {
        wp_send_json_error('Missing or invalid data');
        return;
    }

    // Save individual answer
    $answers_meta_key = '_user_answers_' . $user_id;
    $existing_answers = get_post_meta($lesson_id, $answers_meta_key, true);
    if (!is_array($existing_answers)) {
        $existing_answers = [];
    }
    $existing_answers[$question_id] = $answer;
    update_post_meta($lesson_id, $answers_meta_key, $existing_answers);

    // Calculate and update score
    $questions = maybe_unserialize(get_post_meta($lesson_id, '_interactive_video_questions', true));
    $question = isset($questions[$question_id]) ? $questions[$question_id] : null;
    $score_meta_key = '_user_score_' . $user_id;

    $current_score = (int) get_post_meta($lesson_id, $score_meta_key, true);
    $earned_score = 0;

    if ($question && isset($question['options']) && is_array($question['options'])) {
        foreach ($question['options'] as $opt) {
            if (
                is_array($opt) &&
                isset($opt['text']) &&
                trim(strtolower($opt['text'])) === trim(strtolower($answer))
            ) {
                $earned_score = isset($opt['scores']) ? intval($opt['scores']) : 0;
                break;
            }
        }
    } elseif (!isset($question['options'])) {
        // for text-based answers (if applicable)
        $earned_score = !empty($answer) ? 2 : 0;
    }

    $new_score = $current_score + $earned_score;
    update_post_meta($lesson_id, $score_meta_key, $new_score);

    wp_send_json_success([
        'new_score' => $new_score,
        'added' => $earned_score
    ]);
}


add_action('admin_notices', 'interactive_video_error_notice');
function interactive_video_error_notice()
{
    global $pagenow;

    if ($pagenow !== 'post.php' || !isset($_GET['post'])) {
        return;
    }

    $post_id = intval($_GET['post']);
    $errors = get_transient("interactive_video_error_$post_id");

    if ($errors) {
        echo '<div class="notice notice-error"><ul>';
        foreach ($errors as $error) {
            echo '<li><strong>Interactive Video Error:</strong> ' . esc_html($error) . '</li>';
        }
        echo '</ul></div>';

        delete_transient("interactive_video_error_$post_id");
    }
}
