<!--footer start here-->
<div class="site-footer p-2">
    <div class="container">
        <div class="footer-row d-flex flex-wrap align-items-center justify-content-center">
            <div class="footer-content text-center">
                <p class="mb-0">
                    {{ !empty(company_setting('footer_text', $workspace->created_by, $workspace->id)) ? company_setting('footer_text', $workspace->created_by, $workspace->id) : admin_setting('footer_text') }}
                </p>
            </div>
        </div>
    </div>
</div>
<!--footer end here-->
<!-- modal start -->
<div class="modal fade modal-animate quiz-management-popup" id="quiz-management-modal" tabindex="-1"
    aria-labelledby="quizmanagementmodal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-content-inner p-lg-4 p-3">
                <div class="modal-header border-0">
                    <h3 class="modal-title fw-bold text-center w-100">{{ __('Start Quiz') }}</h3>
                    <button type="submit" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form id="participants-details">
                    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                    <input type="hidden" name="slug" id="slug" value="{{ $slug }}">
                    <input type="hidden" name="quiz_id" id="quiz_id">
                    <div class="modal-body p-0 pt-3 pb-sm-3">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input class="form-control" required id="name" placeholder="{{ __('Enter Name') }}"
                                name="name" type="text" value="">
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input class="form-control" name="email" type="email" id="email"
                                placeholder="{{ __('Enter Email') }}" value="" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="mobile_no" class="form-label">{{ __('Mobile Number') }}</label>
                            <input class="form-control" required type="number" name="mobile_no" id="mobile_no"
                                placeholder="{{ __('Enter Mobile Number') }}">
                        </div>
                        <div class="form-group">
                            <label for="department" class="form-label">{{ __('Department') }}</label>
                            <input class="form-control" name="department" type="text" id="department"
                                placeholder="{{ __('Enter Department') }}" value="">
                        </div>
                        <div class="form-group">
                            <label for="years_at_gtext" class="form-label">{{ __('Years at Gtext') }}</label>
                            <input class="form-control" name="years_at_gtext" type="text" id="years_at_gtext"
                                placeholder="{{ __('Enter Years at Gtext') }}" value="">
                        </div>
                        <div class="form-group">
                            <label for="subsidiary" class="form-label">{{ __('Subsidiary') }}</label>
                            <input class="form-control" name="subsidiary" type="text" id="subsidiary"
                                placeholder="{{ __('Enter Subsidiary') }}" value="">
                        </div>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-center gap-3 border-0 p-0">
                        <button type="submit" class="modal-common-btn mb-0">
                            <span>{{ __('Submit') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- quiz-question-modal start -->
<div class="modal fade modal-animate quiz-question-popup" id="quiz-question-modal" tabindex="-1"
    aria-labelledby="quizmanagementmodal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-content-inner p-lg-4 p-3">
                <div class="modal-header p-0 d-flex align-items-center flex-wrap border-0">
                    <div class="quiz-total-question"></div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="timer">
                        <div class="quiz-timer-left">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_39_157)">
                                    <path
                                        d="M24.0875 8.93031L25.8838 7.13406L24.1163 5.36594L22.1947 7.2875C20.4393 6.04349 18.3908 5.27679 16.25 5.0625V2.5H18.75V0H11.25V2.5H13.75V5.0625C11.6092 5.27679 9.56073 6.04349 7.80532 7.2875L5.88376 5.36594L4.11626 7.13406L5.91251 8.93031C4.23582 10.7056 3.11581 12.933 2.6908 15.3377C2.26579 17.7423 2.55439 20.2187 3.52096 22.4612C4.48753 24.7036 6.08974 26.6138 8.12972 27.9559C10.1697 29.2981 12.5581 30.0133 15 30.0133C17.4419 30.0133 19.8303 29.2981 21.8703 27.9559C23.9103 26.6138 25.5125 24.7036 26.4791 22.4612C27.4456 20.2187 27.7342 17.7423 27.3092 15.3377C26.8842 12.933 25.7642 10.7056 24.0875 8.93031ZM15 27.5C13.0222 27.5 11.0888 26.9135 9.4443 25.8147C7.79981 24.7159 6.51809 23.1541 5.76121 21.3268C5.00434 19.4996 4.8063 17.4889 5.19215 15.5491C5.57801 13.6093 6.53041 11.8275 7.92894 10.4289C9.32746 9.03041 11.1093 8.078 13.0491 7.69215C14.9889 7.3063 16.9996 7.50433 18.8268 8.26121C20.6541 9.01808 22.2159 10.2998 23.3147 11.9443C24.4135 13.5888 25 15.5222 25 17.5C24.997 20.1513 23.9425 22.6931 22.0678 24.5678C20.1931 26.4425 17.6513 27.497 15 27.5Z"
                                        fill="#060606" />
                                    <path
                                        d="M15 10V17.5H7.5C7.5 18.9834 7.93987 20.4334 8.76398 21.6668C9.58809 22.9001 10.7594 23.8614 12.1299 24.4291C13.5003 24.9968 15.0083 25.1453 16.4632 24.8559C17.918 24.5665 19.2544 23.8522 20.3033 22.8033C21.3522 21.7544 22.0665 20.418 22.3559 18.9632C22.6453 17.5083 22.4968 16.0003 21.9291 14.6299C21.3614 13.2594 20.4001 12.0881 19.1668 11.264C17.9334 10.4399 16.4834 10 15 10Z"
                                        fill="#060606" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_39_157">
                                        <rect width="30" height="30" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="quiz-timer-right"></div>
                    </div>
                </div>

                <form id="quiz-form">
                    <div class="modal-body p-0">
                        <div>
                            <div class="quiz-lable"></div>
                            <div class="quiz-question-wrap">
                                <div class="quiz-question-text"></div>
                            </div>
                            <div class="quiz-option-list"></div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-center gap-3 border-0 p-0">
                        <button type="submit" class="modal-common-btn mb-0">
                            <span>{{ __('Next') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- quiz-question-modal end -->

<!-- quiz-complete-modal start -->
  <div class="modal fade modal-animate quiz-complete-popup" id="quiz-complete-modal" tabindex="-1"
      aria-labelledby="quizmanagementmodal" aria-hidden="true">

      <div class="modal-dialog">
          <div class="modal-content quiz-success">
              <div class="modal-content-inner">
                  <div class="modal-header d-flex align-items-center flex-wrap border-0">
                      <h5 class="mb-0">{{ __('Quiz Completed') }}</h5>
                  </div>
                  <div class="modal-body w-100">
                      <div class="quiz-complete-inner text-center">
                          <div class="quiz-complete-content">
                              <h4 class="fw-bold">
                                  {{ __('Thank you for taking the quiz!') }}</h4>
                              <p class="mb-0">
                                  {{ __('Your responses have been submitted. Please wait for further updates or check your results later.') }}
                              </p>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer d-flex align-items-center justify-content-center gap-3 border-0">
                      <button type="button" class="modal-common-btn mb-0" data-bs-dismiss="modal">
                          <span>{{ __('Close') }}</span>
                      </button>
                  </div>
              </div>
          </div>
      </div>
  </div>
<!-- quiz-complete-modal end -->

<script src="{{ asset('Modules/QuizManagement/Resources/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('Modules/QuizManagement/Resources/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('Modules/QuizManagement/Resources/assets/js/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

<script>
    $(document).on("click", ".start-quiz-btn", function() {
        var quizId = $(this).data("id");
        $("#quiz-management-modal").find("input[name='name']").val("");
        $("#quiz-management-modal").find("input[name='email']").val("");
        $("#quiz-management-modal").find("input[name='mobile_no']").val("");
        $("#quiz-management-modal").find("input[name='department']").val("");
        $("#quiz-management-modal").find("input[name='years_at_gtext']").val("");
        $("#quiz-management-modal").find("input[name='subsidiary']").val("");
        if ($("#quiz_id").length) {
            $("#quiz_id").val(quizId);
        }
    });

    $(document).on('submit', '#participants-details', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: "{{ route('quiz.participate.store') }}",
            type: "POST",
            data: formData,
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': jQuery('#token').val()
            },
            success: function(response) {
                if (response.is_success) {
                    $("#quiz-management-modal").modal("hide");
                    $("#quiz-question-modal").modal("show");
                    response.start_time = new Date();
                    loadQuiz(response.quiz, response.questions, response.participant_id, response.workspace_id);
                } else {
                    show_toastr("Error", "An error occurred! Please try again.", "danger");
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    show_toastr("Validation Error", xhr.responseJSON.error, "danger");
                }
            }
        });
    });

    $(document).on('submit', '#quiz-form', function(event) {
        event.preventDefault();
        if (currentQuestion < questions.length - 1) {
            currentQuestion++;
            showQuestion();
        } else {
            $("#quiz-question-modal").modal("hide");
        }
    });

    $(document).ready(function() {
        let quizStarted = false;
        let quizCompleted = false;

        $("#quiz-question-modal").modal({
            backdrop: "static",
            keyboard: false
        });

        $(window).on("beforeunload", function(e) {
            if (quizStarted && !quizCompleted) {
                return "You have an ongoing quiz. Are you sure you want to leave?";
            }
        });

        $("#quiz-question-modal").on("show.bs.modal", function() {
            quizStarted = true;
        });

        $("#quiz-question-modal").on("hide.bs.modal", function(e) {
            if (quizStarted && !quizCompleted) {
                e.preventDefault();
                alert("You cannot close the quiz until you submit your answers.");
            }
        });
    });

    function loadQuiz(quiz, questions, participantId, workspaceId) {
        let currentQuestion = 0;
        let score = 0;
        let correctAnswers = [];
        let timer;
        const timeLimit = quiz.per_qus_time || 60;
        const startTime = new Date().toLocaleTimeString('en-GB');

        function showQuestion() {
            clearInterval(timer);
            if (currentQuestion >= questions.length) {
                const endTime = new Date().toLocaleTimeString('en-GB');
                submitQuizResults(participantId, quiz.id, workspaceId, correctAnswers, startTime, endTime);
                return;
            }
            const q = questions[currentQuestion];
            $(".quiz-lable").text(`Question ${currentQuestion + 1}`);
            $(".quiz-question-text").text(`Q${currentQuestion + 1}: ${q.question}`);
            $(".quiz-option-list").empty();
            $(".quiz-total-question").text(`${currentQuestion + 1}/${questions.length}`);
            updateProgressBar();

            if (currentQuestion === questions.length - 1) {
                $(".modal-common-btn span").text("Complete Quiz");
            } else {
                $(".modal-common-btn span").text("Next");
            }

            if (q.options && q.options.values) {
                if (q.options.type === 'mcq') {
                    if ($(".quiz-option-list").length > 0) {
                        Object.entries(q.options.values).forEach(([key, value]) => {
                            $(".quiz-option-list").append(
                                `<div class="option mcq-option" data-answer="${value}" data-letter="${key}">
                                    <div class="option-content">${value}</div>
                                </div>`
                            );
                        });
                    }
                } else {
                    Object.entries(q.options.values).forEach(([key, value]) => {
                        $(".quiz-option-list").append(
                            `<div class="option" data-answer="${value}">
                                <div class="option-content">${value}</div>
                            </div>`
                        );
                    });
                }

                $(".option").off("click").on("click", function() {
                    selectOption($(this), q.correct_answer, q.id);
                });
            } else {
                $(".quiz-option-list").append(
                    `<input type="text" id="descriptive-answer" class="form-control" placeholder="Type your answer here">`
                );
                $("#descriptive-answer").off("keypress blur").on("keypress blur", function(event) {
                    if (event.type === "blur" || event.which === 13) {
                        let userAnswer = $(this).val().trim();
                        if (userAnswer) checkDescriptiveAnswer(userAnswer, q.correct_answer, q.id);
                    }
                });
            }
            startTimer(timeLimit);
        }

        function checkDescriptiveAnswer(userAnswer, correctAnswer, questionId) {
            clearInterval(timer);
            const answerField = $("#descriptive-answer");
            if (userAnswer.toLowerCase() === correctAnswer.toLowerCase()) {
                answerField.addClass("correct");
                score++;
                correctAnswers.push(questionId);
            } else {
                answerField.addClass("incorrect");
                alert(`❌ Incorrect! The correct answer is: ${correctAnswer}`);
            }
            setTimeout(() => {
                currentQuestion++;
                showQuestion();
            }, 1500);
        }

        function selectOption(selectedOption, correctAnswer, questionId) {
            clearInterval(timer);
            const selectedAnswer = selectedOption.data("answer");
            $(".option").removeClass("correct incorrect");
            const correctIcon = `<div class="correct-icon">
                                <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.67623 12.0016C7.17275 9.701 12.6773 3.77901 17.2421 0.702265C17.436 0.571585 17.6549 0.828265 17.4941 0.997825C13.1576 5.57774 8.52203 10.758 5.85983 15.2374C5.78567 15.3622 5.60675 15.3665 5.52917 15.2437C4.18961 13.1219 3.04067 10.0718 0.577908 9.13689C0.395028 9.06741 0.412488 8.80874 0.602748 8.76302C2.96273 8.19422 4.06865 10.2127 5.67623 12.0012V12.0016Z" fill="black"/>
                                </svg>
                            </div>`;
            const incorrectIcon = `<div class="incorrect-icon">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.82324 15.0558C3.59824 15.0558 3.37324 14.97 3.20168 14.7984C2.85855 14.4553 2.85855 13.8988 3.20168 13.5553L13.5555 3.20177C13.8987 2.85865 14.4552 2.85865 14.7987 3.20177C15.1418 3.5449 15.1418 4.10142 14.7987 4.4449L4.44445 14.7984C4.36298 14.8801 4.26615 14.9449 4.15954 14.9891C4.05293 15.0333 3.93864 15.0559 3.82324 15.0558Z" fill="white"/>
                                    <path d="M14.1768 15.0556C13.9518 15.0556 13.7268 14.9699 13.5552 14.7983L3.20168 4.44444C2.85855 4.10131 2.85855 3.54479 3.20168 3.20131C3.5448 2.85819 4.10133 2.85819 4.4448 3.20131L14.7987 13.5552C15.1418 13.8983 15.1418 14.4548 14.7987 14.7983C14.717 14.88 14.6201 14.9448 14.5134 14.989C14.4066 15.0331 14.2923 15.0558 14.1768 15.0556Z" fill="white"/>
                                </svg>
                            </div>`;
            if (String(selectedAnswer) === String(correctAnswer)) {
                selectedOption.addClass("correct");
                selectedOption.append(correctIcon);
                score++;
                correctAnswers.push(questionId);
            } else {
                selectedOption.addClass("incorrect");
                selectedOption.append(incorrectIcon);
                const correctOption = $(`.option[data-answer="${correctAnswer}"]`);
                if (correctOption.length) {
                    correctOption.addClass("correct");
                    correctOption.append(correctIcon);
                }
            }

            setTimeout(() => {
                currentQuestion++;
                showQuestion();
            }, 1000);
        }

        function submitQuizResults(participantId, quizId, workspaceId, correctAnswers, startTime, endTime) {
            $.ajax({
                url: "{{ route('quiz.results.store') }}",
                type: "POST",
                data: {
                    participant_id: participantId,
                    quiz_id: quizId,
                    workspace_id: workspaceId,
                    correct_answers: correctAnswers,
                    start_time: startTime,
                    end_time: endTime,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    quizCompleted = true;
                    quizStarted = false;
                    $(window).off("beforeunload");
                    $("#quiz-question-modal").off("hide.bs.modal");
                    $("#quiz-question-modal").modal("hide");
                    $('.quiz-total-count').html(response.score);
                    if (response.status == 'Failed') {
                        $('.quiz-passed').addClass('d-none');
                        $('.quiz-failed').removeClass('d-none');
                    } else {
                        $('.quiz-failed').addClass('d-none');
                        $('.quiz-passed').removeClass('d-none');
                    }
                    $("#quiz-complete-modal").modal("show");
                    $(".total-correct-ans").text(`${score}/${questions.length}`);
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        show_toastr("Validation Error", xhr.responseJSON.error, "danger");
                    }
                }
            });
        }

        function formatTime(seconds) {
            let minutes = Math.floor(seconds / 60);
            let secs = seconds % 60;
            return `${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
        }

        function startTimer(time) {
            clearInterval(timer);
            $(".quiz-timer-right").text(formatTime(time));

            timer = setInterval(() => {
                time--;
                $(".quiz-timer-right").text(formatTime(time));
                if (time <= 0) {
                    clearInterval(timer);
                    currentQuestion++;
                    showQuestion();
                }
            }, 1000);
        }

        function updateProgressBar() {
            let progress = ((currentQuestion + 1) / questions.length) * 100;
            $(".progress-bar").css("width", `${progress}%`).attr("aria-valuenow", progress);
        }

        showQuestion();
    }
</script>
<script>
    function show_toastr(title, message, type) {
        var o, i;
        var icon = '';
        var cls = '';
        if (type == 'success') {
            icon = 'fas fa-check-circle';
            cls = 'success';
        } else {
            icon = 'fas fa-times-circle';
            cls = 'danger';
        }
        $.notify({
            icon: icon,
            title: " " + title,
            message: message,
            url: ""
        }, {
            element: "body",
            type: cls,
            allow_dismiss: !0,
            placement: {
                from: 'top',
                align: 'right'
            },
            offset: {
                x: 15,
                y: 15
            },
            spacing: 10,
            z_index: 1080,
            delay: 2500,
            timer: 2000,
            url_target: "_blank",
            mouse_over: !1,
            animate: {
                enter: o,
                exit: i
            },
            template: '<div class="alert alert-{0} alert-icon alert-group alert-notify" data-notify="container" role="alert"><div class="alert-group-prepend alert-content"><span class="alert-group-icon"><i data-notify="icon"></i></span></div><div class="alert-content"><strong data-notify="title">{1}</strong><div data-notify="message">{2}</div></div><button type="button" class="close" data-notify="dismiss" aria-label="Close"><span aria-hidden="true">×</span></button></div>'
        });
    }
</script>
<!-- script end -->