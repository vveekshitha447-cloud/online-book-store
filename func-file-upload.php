<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
# File upload helper function 
function upload_file($files, $allowed_exs, $path){
   $file_name = $files['name'];
   $tmp_name  = $files['tmp_name'];
   $error     = $files['error'];
   if ($error === 0) {
      $file_ex    = pathinfo($file_name, PATHINFO_EXTENSION);
      $file_ex_lc = strtolower($file_ex);
      if (in_array($file_ex_lc, $allowed_exs)) {
         $new_file_name    = uniqid("", true) . '.' . $file_ex_lc;
         $base             = dirname(__DIR__); // resolves to /onlinebookstore/
         $file_upload_path = $base . '/uploads/' . $path . '/' . $new_file_name;
         if (move_uploaded_file($tmp_name, $file_upload_path)) {
            $sm['status'] = 'success';
            $sm['data']   = $new_file_name;
            return $sm;
         } else {
            $em['status'] = 'error';
            $em['data']   = 'Upload failed! Check folder permissions.';
            return $em;
         }
      } else {
         $em['status'] = 'error';
         $em['data']   = "You can't upload files of this type";
         return $em;
      }
   } else {
      $em['status'] = 'error';
      $em['data']   = 'Error occurred while uploading!';
      return $em;
   }
}