<?php
AddEventHandler("catalog", "OnSuccessCatalogImport1C", "AddCategoryPictureGallery");
function AddCategoryPictureGallery()
{
    // Путь до архива
    $sFilePathArc = $_SERVER["DOCUMENT_ROOT"] . "/upload/1c_catalog/groupImages.zip";
    // Путь до каталога, в который извлекаем файлы изображений
    $sFilePathDst = $_SERVER["DOCUMENT_ROOT"] . "/images/category";

    // Распаковываем архив
    $resArchiver = CBXArchive::GetArchive($sFilePathArc);
    $resArchiver->Unpack($sFilePathDst);

    // Получаем имена файлов в папке
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/images/category';
    function myscandir($dir, $sort = 0)
    {
        $list = scandir($dir, $sort);

        if (!$list) {
            return false;
        }

        if ($sort == 0) {
            unset($list[0], $list[1]);
        } else {
            unset($list[count($list) - 1], $list[count($list) - 1]);
        }
        return $list;
    }

    // Переименовываем файлы, чтобы в имени был только внешний код категории
    $pictures = myscandir($dir);
    $cnt = 1;
    $arr_id = [];
    foreach ($pictures as $picture) {
        $pic_name = explode("_", $picture);
        // Проверем расширение картинки, если JPG
        if (strpos($pic_name[1], 'jpg')) {
            rename(
                $_SERVER['DOCUMENT_ROOT'] . '/images/category/' . $picture,
                $_SERVER['DOCUMENT_ROOT'] . '/images/category/' . $pic_name[0] .'_'.$cnt.'.jpg'
            );
        }
        // Проверем расширение картинки, если PNG
        if (strpos($pic_name[1], 'png')) {
            rename(
                $_SERVER['DOCUMENT_ROOT'] . '/images/category/' . $picture,
                $_SERVER['DOCUMENT_ROOT'] . '/images/category/' . $pic_name[0] .'_'.$cnt.'.png'
            );
        }
        $cnt++;
        // Собираем массив внешних кодов категорий
        array_push($arr_id, $pic_name[0]);
    }
    $ids = array_unique($arr_id);
    // Перебираем внешние коды и сопоставляем с загруженными картинками
    foreach($ids as $id){
        $names = myscandir($dir);
        $arr = [];
        foreach($names as $name){
            $id_name = explode("_", $name);
            if($id_name[0] == $id){
                array_push($arr, $name);
            }
        }
        $sections = CIBlockSection::GetList(
            Array("ID" => "ASC"),
            Array("IBLOCK_ID" => 20, "XML_ID" => $id),
            false,
            Array('ID')
        );
        while ($ar_fields = $sections->GetNext()) {
            $bs = new CIBlockSection;
            $arFields = Array(
                "PICTURE" => CFile::MakeFileArray(
                    $_SERVER['DOCUMENT_ROOT'] . '/images/category/' . $arr[0]
                ),
                "DETAIL_PICTURE" => CFile::MakeFileArray(
                    $_SERVER['DOCUMENT_ROOT'] . '/images/category/' . $arr[0]
                ),
                "UF_BG_IMAGE" => array(
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[0]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[1]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[2]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[3]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[4]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[5]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[6]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[7]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[8]
                    ),
                    CFile::MakeFileArray(
                        $_SERVER['DOCUMENT_ROOT'] . '/images/category/'.$arr[9]
                    ),
                ),
            );
            // Обновляем картинку категории
            $bs->Update($ar_fields['ID'], $arFields);
        }
    }

    //Удаляем временную папку
    function my_delete_dir($mypath){
        $dir = opendir($mypath);
        while (($file = readdir($dir))){
            if (is_file($mypath."/".$file))
                unlink ($mypath."/".$file);
            elseif (is_dir($mypath."/".$file) && ($file != ".") && ($file != ".."))
                my_delete_dir ($mypath."/".$file);
        }
        closedir ($dir);
        rmdir ($mypath);
    }
    my_delete_dir($sFilePathDst);
}