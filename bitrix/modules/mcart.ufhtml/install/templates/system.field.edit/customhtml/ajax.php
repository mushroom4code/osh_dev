<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if($_POST['fieldName']) {
    CModule::IncludeModule("fileman");
    $LHE = new CLightHTMLEditor;
    $LHE->Show(array(
        'id' => '',
        'width' => '100%',
        'height' => '150px',
        'inputName' => $_POST['fieldName'],
        'content' => '',
        'bUseFileDialogs' => false,
        'bFloatingToolbar' => false,
        'bArisingToolbar' => false,
        'toolbarConfig' => array(
            'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'Code', 'Source', 'Video', 'Html',
            'CreateLink', 'DeleteLink', 'Image', 'Video',
            'BackColor', 'ForeColor',
            'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
            'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
            'StyleList', 'HeaderList',
            'FontList', 'FontSizeList',
        ),
    ));
}

