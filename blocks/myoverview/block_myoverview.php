<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains the class for the My overview block.
 *
 * @package    block_myoverview
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * My overview block class.
 *
 * @package    block_myoverview
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myoverview extends block_base {

    /**
     * Init.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_myoverview');
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }
    
        // Get user preferences for grouping, sorting, view, etc.
        $group = get_user_preferences('block_myoverview_user_grouping_preference');
        $sort = get_user_preferences('block_myoverview_user_sort_preference');
        $view = get_user_preferences('block_myoverview_user_view_preference');
        $paging = get_user_preferences('block_myoverview_user_paging_preference');
        $customfieldvalue = get_user_preferences('block_myoverview_user_grouping_customfieldvalue_preference');
    
        // Fetch courses (Replace this with your preferred method to get courses)
        $courses = enrol_get_my_courses(); // This gets the user's enrolled courses
        $data = [];
    
        foreach ($courses as $course) {
            // Add custom course data, such as summary and image
            $data[] = [
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'viewurl' => new moodle_url('/course/view.php', ['id' => $course->id]),
                'summary' => $course->summary, // Add course summary
                'image' => $this->get_course_image($course->id) // Use custom function to fetch course image
            ];
        }
    
        // Pass the data to the renderable
        $renderable = new \block_myoverview\output\main($group, $sort, $view, $paging, $customfieldvalue, $data);
    
        // Use renderer to output the content
        $renderer = $this->page->get_renderer('block_myoverview');
    
        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';
    
        return $this->content;
    }
    
    /**
     * Fetches the course image URL for a given course ID.
     * 
     * @param int $courseid The ID of the course.
     * @return string The URL of the course image or a fallback image.
     */
    private function get_course_image($courseid) {
        global $CFG;
        $context = context_course::instance($courseid);
    
        // Get the file storage instance
        $fs = get_file_storage();
    
        // Look for files in the 'course' component and 'overviewfiles' file area
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'sortorder', false);
    
        // Loop through files to find a valid image
        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
            }
        }
    
        // Return a fallback image if no valid image is found
        return $CFG->wwwroot . '/theme/image.php?image=fallback';
    }
    

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Allow the block to have a configuration page.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = get_config('block_myoverview');

        // Get the customfield values (if any).
        if ($configs->displaygroupingcustomfield) {
            $group = get_user_preferences('block_myoverview_user_grouping_preference');
            $sort = get_user_preferences('block_myoverview_user_sort_preference');
            $view = get_user_preferences('block_myoverview_user_view_preference');
            $paging = get_user_preferences('block_myoverview_user_paging_preference');
            $customfieldvalue = get_user_preferences('block_myoverview_user_grouping_customfieldvalue_preference');

            $renderable = new \block_myoverview\output\main($group, $sort, $view, $paging, $customfieldvalue);
            $customfieldsexport = $renderable->get_customfield_values_for_export();
            if (!empty($customfieldsexport)) {
                $configs->customfieldsexport = json_encode($customfieldsexport);
            }
        }

        return (object) [
            'instance' => new stdClass(),
            'plugin' => $configs,
        ];
    }

    /**
     * Disable block editing on the my courses page.
     *
     * @return boolean
     */
    public function instance_can_be_edited() {
        if ($this->page->blocks->is_known_region(BLOCK_POS_LEFT) || $this->page->blocks->is_known_region(BLOCK_POS_RIGHT)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hide the block header on the my courses page.
     *
     * @return boolean
     */
    public function hide_header() {
        if ($this->page->blocks->is_known_region(BLOCK_POS_LEFT) || $this->page->blocks->is_known_region(BLOCK_POS_RIGHT)) {
            return false;
        } else {
            return true;
        }
    }
}

