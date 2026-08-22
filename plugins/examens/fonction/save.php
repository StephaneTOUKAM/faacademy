<?php
    require_once(dirname(__FILE__,5).'/wp-load.php');
    $userdata = array(
        'user_login' =>  $_POST['status'] == 1 ? $_POST['firstname'] : $_POST['assocname'],
        'user_email' =>  $_POST['status'] == 1 ? $_POST['email'] : $_POST['emailperson'],
        'user_pass'  =>  $_POST['password']
    );
     
    $user_id = wp_insert_user( $userdata ) ;
     
    // On success.
    if ( ! is_wp_error( $user_id ) ) {
        wp_update_user( array ('ID' => $user_id, 'role' => $_POST['status'] == 1 ? 'particulier' : 'association') ) ;
        add_user_meta( $user_id, 'firstname', $_POST['firstname']);
        add_user_meta( $user_id, 'lastname', $_POST['lastname']);
        add_user_meta( $user_id, 'dob', $_POST['dob']);
        add_user_meta( $user_id, 'idnumber', $_POST['idnumber']);
        add_user_meta( $user_id, 'email', $_POST['email']);
        add_user_meta( $user_id, 'city', $_POST['city']);
        add_user_meta( $user_id, 'zipcode', $_POST['zipcode']);
        add_user_meta( $user_id, 'pays', $_POST['pays']);
        add_user_meta( $user_id, 'cellphone', $_POST['cellphone']);
        add_user_meta( $user_id, 'homephone', $_POST['homephone']);
        add_user_meta( $user_id, 'mailaddress', $_POST['mailaddress']);
        add_user_meta( $user_id, 'corigin', $_POST['corigin']);
        add_user_meta( $user_id, 'assocname', $_POST['assocname']);
        add_user_meta( $user_id, 'regnumber', $_POST['regnumber']);
        add_user_meta( $user_id, 'dateformed', $_POST['dateformed']);
        add_user_meta( $user_id, 'physicaddress', $_POST['physicaddress']);
        add_user_meta( $user_id, 'cityentreprise', $_POST['cityentreprise']);
        add_user_meta( $user_id, 'zipcodeentreprise', $_POST['zipcodeentreprise']);
        add_user_meta( $user_id, 'numberaffiliate', $_POST['numberaffiliate']);
        add_user_meta( $user_id, 'paysentreprise', $_POST['paysentreprise']);
        add_user_meta( $user_id, 'password', $_POST['password']);
        add_user_meta( $user_id, 'firstnameperson', $_POST['firstnameperson']);
        add_user_meta( $user_id, 'lastnameperson', $_POST['lastnameperson']);
        add_user_meta( $user_id, 'emailperson', $_POST['emailperson']);
        add_user_meta( $user_id, 'mailperson', $_POST['mailperson']);
        add_user_meta( $user_id, 'cityperson', $_POST['cityperson']);
        add_user_meta( $user_id, 'zipcodeperson', $_POST['zipcodeperson']);
        add_user_meta( $user_id, 'cellphoneperson', $_POST['cellphoneperson']);
        // if ($_POST['status'] == 1) {
            $my_post = array(
                'post_title'    => 'Souscription assurance rapatriement '.$_POST['firstname']. ' '.$user_id,
                'post_content'  => $_POST['status'] == 1 ? $_POST['firstname'].''.$_POST['lastname'] : $_POST['assocname'],
                'post_type'  => 'souscription',
                'post_excerpt'  => 'Assurance rapatriement',
                'post_status'  => 'publish',
                'comment_count'   => $_POST['paye'] == 'Yes' ? 1 : 0,
                'post_author'   => $user_id
            );
            wp_insert_post( $my_post );
        // }
           
        // Insert the post into the database
        $response = [
            "type"=>"success"
        ];
        echo json_encode($response);
    }else {
        $response = [
            "type"=>"error"
        ];
        echo json_encode($response);
    }