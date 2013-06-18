<?php

// check that contact/email is defined instead of contact/mail
if(! function_exists("email_instead_of_mail_in_contact"))
{
    function email_instead_of_mail_in_contact($space_api_file, &$errors, &$warnings, &$valid_versions, &$invalid_versions)
    {
        global $logger;
        $logger->logDebug("Processing the plugin 'long_not_allowed_on_top_level'");

        $obj = $space_api_file->json();

        // merge both arrays to get all the versions, prior checks might
        // have moved versions from valid to invalid and thus we would not
        // check versions that already have been marked invalid but we need
        // to check those too to assign these versions the error messages
        $versions = array_merge($valid_versions, $invalid_versions);

        $versions_of_interest = array();
        foreach(preg_replace("/0./", "", $versions) as $version)
            if($version >= 9)
                $versions_of_interest[] = $version;


        // iterate over all the versions where this check makes sense
        foreach($versions_of_interest as $version)
        {
            $extended_version = "0.$version";

            if(property_exists($obj, "contact") && property_exists($obj->contact, "mail"))
            {
                // remove the version from the valid versions array
                $pos = array_search("0.$version", $valid_versions);
                if($pos !== false)
                    array_splice($valid_versions, $pos, 1);

                // add it to the invalid versions array if
                // it's not yet present
                if( false === array_search("0.$version", $invalid_versions))
                    $invalid_versions[] = "0.$version";

                // get the error message array of the current iterated version
                if(property_exists($errors, $extended_version))
                {
                    $a = $errors->$extended_version;
                }
                else
                    $a = array();

                // create the error object consisting of a message and a description
                $err = new stdClass;
                $err->msg = "Property 'contact/mail' must be 'contact/email'.<!--We have found out that you wanted to specify 'email' instead of 'mail' in the contact object. Just add an 'e' :-)-->";
                $err->description = "";

                // add the new error object
                $a[] = $err;

                // assign the new array to the errors object
                $errors->$extended_version = $a;
            }
        }

        return true;
    }

    $space_api_validator->register("email_instead_of_mail_in_contact");
}