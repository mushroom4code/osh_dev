<?
//version 1.0.3
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("fileman"))return;
?>

<div class="fields string" id="main_<?=$arParams["userField"]["FIELD_NAME"]?>"><?
foreach ($arResult["value"] as $res):
?><div class="fields string">
    <?
    $LHE = new CLightHTMLEditor;
    $LHE->Show([
        "width" => "100%",
        "height" => "150px",
        "inputName" => $arParams["userField"]["FIELD_NAME"],
        "content" => htmlspecialcharsback($res),
        "bUseFileDialogs" => false,
        "bFloatingToolbar" => false,
        "bArisingToolbar" => false,
        "toolbarConfig" => [
            "Bold", "Italic", "Underline", "Strike", "RemoveFormat", "Code", "Source", "Video", "Html",
            "CreateLink", "DeleteLink", "Image", "Video",
            "BackColor", "ForeColor",
            "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyFull",
            "InsertOrderedList", "InsertUnorderedList", "Outdent", "Indent",
            "StyleList", "HeaderList",
            "FontList", "FontSizeList",
        ],
    ]);
    ?>

    </div>
<?
endforeach;
?></div>
<?if ($arParams["userField"]["MULTIPLE"] == "Y"):?>

   <?$templateUrl = $this->GetFolder();?>
<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElement('<?=$arParams["userField"]["FIELD_NAME"]?>', this, '<?=$templateUrl?>')">
<?endif;?>
    <script>
        document.querySelectorAll('input[name="<?=$arParams["userField"]["FIELD_NAME"]?>"]').forEach((item) => {
            Object.defineProperty(item, "value", {
                set:  function (t) {
                    this.setAttribute("value", t);
                    BX.fireEvent(this, "change");
                },
                get: function(){
                    return this.getAttribute("value");
                }
            });
        })
    </script>

<?php
