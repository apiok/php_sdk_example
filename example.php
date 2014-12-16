<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>PHP SDK Example</title>
        <link href="http://www.odnoklassniki.ru/oauth/resources.do?type=css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php
        /*
        You have to add this code once before first usage of SDK. Change path to YOUR SDK path in require directive. 
        It connects SDK and checks curl lib is installed.
        */
        /*
        Этот код необходимо добавить один раз перед первым использованием SDK, указав в директиве require путь к файлу с SDK.
        Он подключает SDK и проверяет, установлена ли необходимая для работы SDK библиотека curl.
        */
        require("./odnoklassniki_sdk.php");
        if (!OdnoklassnikiSDK::checkCurlSupport()){
            print "У вас не установлен модуль curl, который требуется для работы с SDK одноклассников.  Инструкция по установке есть, например, <a href=\"http://www.php.net/manual/en/curl.installation.php\">здесь</a>.";
            return;
        }
        ?>
        <?php
        /*
        This is example of using SDK.
        */
        /*
        Это пример использования SDK.
        */
        $template = "<div id=\"t\"><div id=\"tr\"><div class=\"tc\" id=\"tc1\"><img src=\"%s\" class=\"pic\" alt=\"user photo\">%s</div><div class=\"tc\" id=\"tc2\">дружит с</div><div class=\"tc\" id=\"tc3\"><img src=\"%s\" class=\"pic\" alt=\"user photo\">%s</div></div></div>";
        // method checks if request has parameter code
        // если в запросе есть параметр code (считается, что параметр получен после авторизации пользователя на ok)
        if (!is_null(OdnoklassnikiSDK::getCode())){
            if(OdnoklassnikiSDK::changeCodeToToken(OdnoklassnikiSDK::getCode())){
                // example of using makeRequest with parameters
                // method returns required info about current user
                // пример вызова метода с параметрами
                // запрашиваем информацию о текущем пользователе
                $current_user = OdnoklassnikiSDK::makeRequest("users.getCurrentUser", array("fields" => "name,pic_5"));
                // example of using makeRequest without parameters
                // method returns list of user's friends
                // пример вызова метода без параметров
                // запрашиваем списко друзей пользователя
                $friends = OdnoklassnikiSDK::makeRequest("friends.get");
                // method returns required info about required users
                // запрашиваем имя и ссылку на фото первого друга из списка
                $first_friend = OdnoklassnikiSDK::makeRequest("users.getInfo", array("fields" => "name,pic_5", "uids" => $friends[0]))[0];
                printf($template, $current_user["pic_5"], $current_user["name"], $first_friend["pic_5"], $first_friend["name"]);
            }
        } else {
                printf("<div><a class=\"odkl-oauth-lnk\" href=\"%s\"></a></div>", OdnoklassnikiSDK::getAuthorizeUrl());
        }
        ?>
    </body>
</html>