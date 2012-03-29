<?php

function hj_places_setup() {
    if (elgg_is_logged_in()) {
        hj_places_setup_place_form();
        elgg_set_plugin_setting('hj:places:setup', true);
        return true;
    }
    return false;
}

function hj_places_setup_place_form() {
    elgg_load_library('hj:places:base');

    $form = new hjForm();
    $form->title = 'hypeMaps:place';
    $form->label = 'Add New Place';
    $form->description = '';
    $form->subject_entity_subtype = 'hjplace';
    $form->notify_admins = false;
    $form->add_to_river = true;
    $form->comments_on = true;
    $form->ajaxify = true;

    if ($form->save()) {
        $form->addField(array(
            'title' => 'Name',
            'name' => 'title',
            'mandatory' => true
        ));

        $form->addField(array(
            'title' => 'Location',
            'name' => 'location',
            'input_type' => 'location',
            'mandatory' => true
        ));

        $form->addField(array(
            'title' => 'Tags',
            'input_type' => 'tags',
            'name' => 'tags'
        ));

        $form->addField(array(
            'title' => 'Type',
            'input_type' => 'dropdown',
            'name' => 'markertype',
            'mandatory' => true,
            'options_values' => 'hj_places_get_marker_types();'
        ));
        $form->addField(array(
            'title' => 'Description',
            'input_type' => 'longtext',
            'class' => 'elgg-input-longtext',
            'name' => 'description'
        ));
        $form->addField(array(
            'title' => 'Access Level',
            'input_type' => 'access',
            'name' => 'access_id'
        ));

        return true;
    }
    return false;
}