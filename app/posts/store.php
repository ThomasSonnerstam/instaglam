<?php

declare(strict_types=1);

require __DIR__.'/../autoload.php';

if (!isLoggedIn()) {
    redirect('/');
}

if (isset($_FILES['post_image'], $_POST['post_content'])) {
    $content = filter_var($_POST['post_content'], FILTER_SANITIZE_STRING);
    $image = $_FILES['post_image'];
    $id = $_SESSION['user']['id'];

    $imageName = $image['name'];
    $imageTmpName = $image['tmp_name'];
    $imageSize = $image['size'];
    $imageError = $image['error'];
    $imageType = $image['type'];
    $imageExt = explode('.', $imageName);
    $imageActualExt = strtolower(end($imageExt));
    $allowed = ['jpg', 'jpeg', 'png'];
    $date = date("ydm-His");

    // Checking if the uploaded file has the right format
    if (in_array($imageActualExt, $allowed)) {

        // Checking if there was no errors while uploading the file
        if ($imageError === 0) {

            // Checking if the uploaded file has the right file size
            if ($imageSize < 300000) {

                $imageNameNew = time().".".$id.".".$imageActualExt;
                $imageDestination = '/../../uploads/'.$imageNameNew;
                move_uploaded_file($imageTmpName, __DIR__.$imageDestination);

                // Updates the profile picture in the database
                $statement = $pdo->prepare('INSERT INTO posts (user_id, content, image, date_created) VALUES (:user_id, :content, :image, :date_created)');
                if (!$statement) {
                    die(var_dump($pdo->errorInfo()));
                }
                $statement->execute([
                    ':user_id' => $id,
                    ':content' => $content,
                    ':image' => $imageNameNew,
                    ':date_created' => $date,
                    ]);
                redirect('/../../feed.php');
            } else {
                $_SESSION['error'] = 'Your file was too big!';
            }
        } else {
            $_SESSION['error'] = 'There was an error uploading your file!';
        }

    } else {
        $_SESSION['error'] = 'You cannot upload this type of file!';
    }
}
redirect('/../../create-post.php');
