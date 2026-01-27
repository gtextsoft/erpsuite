<?php

namespace Modules\QuizManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\LandingPage\Entities\MarketplacePageSetting;


class MarketPlaceSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $module = 'QuizManagement';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Quiz Management';
        $data['product_main_description'] = '<p>Quiz Management is a system for creating, organizing, and evaluating quizzes efficiently. It allows administrators to manage questions, participants, and results while tracking performance seamlessly.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Dash SaaS Quiz Management Module';
        $data['dedicated_theme_description'] = "<p>Manage quizzes, track progress, and enhance participant engagement effortlessly with Modules Dash's Quiz Management Module.</p>";
        $data['dedicated_theme_sections'] = '[
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Efficient Quiz Management with Dash SaaS",
                                                    "dedicated_theme_section_description": "The Quiz Management add-on in Dash SaaS provides a structured and user-friendly approach to creating, managing, and evaluating quizzes. Whether you are an educator, a corporate trainer, or a business looking to conduct employee assessments, this add-on simplifies the entire quiz process. From setting up questions to tracking participant performance, it offers a complete solution that enhances engagement and productivity. By automating key aspects of quiz management, businesses and institutions can save time and ensure a smooth experience for both quiz creators and participants.",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": null,
                                                        "description": null
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Comprehensive Quiz Creation and Management",
                                                    "dedicated_theme_section_description": "With the Quiz Management add-on, users can design quizzes that suit a variety of purposes, including employee skill assessments, student evaluations, and interactive learning sessions. The add-on supports multiple question formats, such as multiple-choice, true/false, and custom input responses, allowing flexibility in quiz structure. Additionally, quiz creators can set timers, define scoring methods, and establish participation criteria to align with specific learning or evaluation objectives. The ability to customize quizzes ensures that they remain engaging, relevant, and effective for participants. ",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": null,
                                                        "description": null
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Organized Question and Participant Handling",
                                                    "dedicated_theme_section_description": "Managing quiz content and participants is streamlined with this add-on, making it easier to handle large volumes of questions and test-takers. Users can categorize and store questions in a well-organized repository, making them easily accessible for future use. The option to shuffle questions and randomize answer orders helps maintain fairness and prevent predictability in assessments. Furthermore, administrators can assign quizzes to specific individuals, teams, or departments, ensuring that the right participants receive the appropriate assessments. This level of control enhances efficiency and ensures that quiz distribution is both structured and effective. ",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": null,
                                                        "description": null
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Automated Quiz Results and Reporting",
                                                    "dedicated_theme_section_description": "One of the most valuable features of the Quiz Management add-on is its ability to automate result generation and reporting. Once participants complete a quiz, responses are evaluated instantly, reducing the need for manual grading. The system provides real-time feedback, helping participants understand their performance immediately. Additionally, detailed reports offer insights into participant scores, completion times, and overall trends, allowing administrators to make data-driven decisions. These analytics help in identifying knowledge gaps, improving training programs, and refining future assessments to achieve better learning outcomes. ",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": null,
                                                        "description": null
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Versatile Solution for Businesses and Educators",
                                                    "dedicated_theme_section_description": "Designed to meet the needs of both businesses and educators, the Quiz Management add-on is a powerful tool for conducting structured assessments. Organizations can use it for employee training, compliance testing, and certification programs, while educational institutions can leverage it for student learning evaluations. The add-on ensures that quizzes are not only easy to create but also effective in measuring knowledge and progress. By providing a reliable way to manage quizzes and track performance, this solution helps businesses and educators maintain high standards in learning and development. ",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": null,
                                                        "description": null
                                                    }
                                                    }
                                                }
                                            ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"QuizManagement"},{"screenshots":"","screenshots_heading":"QuizManagement"},{"screenshots":"","screenshots_heading":"QuizManagement"},{"screenshots":"","screenshots_heading":"QuizManagement"},{"screenshots":"","screenshots_heading":"QuizManagement"}]';
        $data['addon_heading'] = 'Why choose dedicated modules<b> for Your Business?</b></h2>';
        $data['addon_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['whychoose_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with DASH';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Accounting, HR, Payments, Leads, Communication, Management, and more, all in one place!</p>';
        $data['pricing_plan_demo_link'] = '#';
        $data['pricing_plan_demo_button_text'] = 'View Live Demo';
        $data['pricing_plan_text'] = '{"1":{"title":"Pay-as-you-go"},"2":{"title":"Unlimited installation"},"3":{"title":"Secure cloud storage"}}';
        $data['whychoose_sections_status'] = 'on';
        $data['dedicated_theme_section_status'] = 'on';

        foreach ($data as $key => $value) {
            if (!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', $module)->exists()) {
                MarketplacePageSetting::updateOrCreate(
                    [
                        'name' => $key,
                        'module' => $module
                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }
}
