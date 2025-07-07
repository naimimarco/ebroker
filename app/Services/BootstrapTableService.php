<?php

namespace App\Services;

class BootstrapTableService
{
    private static string $defaultClasses = "btn btn-xs btn-rounded btn-icon mb-2";

    /**
     * @param string $iconClass
     * @param string $url
     * @param array $customClass
     * @param array $customAttributes
     * @return string
     */
    public static function button(string $iconClass, string $url, array $customClass = [], array $customAttributes = [], string $iconText = '')
    {
        $customClassStr = implode(" ", $customClass);
        $class = self::$defaultClasses . ' ' . $customClassStr;
        $attributes = '';
        if (count($customAttributes) > 0) {
            foreach ($customAttributes as $key => $value) {
                $attributes .= $key . '="' . $value . '" ';
            }
        }
        if(!empty($iconText)){
            $iconTextElement = '<span class="icon_text">' . $iconText . '</span>';
        }else{
            $iconTextElement = '';
        }
        return '<a href="' . $url . '" class="' . $class . '" ' . $attributes . '><i class="' . $iconClass . '"></i>'.$iconTextElement.'</a>';
    }

    /**
     * @param $url
     * @param bool $modal
     * @return string
     */
    public static function editButton($url, bool $modal = false, $dataBsTarget = null, $customClass = null, $id = null, $onClick = null, $data_types = '', $iconClass = null, $iconText = '')
    {
        $customClass = ["btn icon btn-primary btn-sm rounded-pill edit_btn " . $customClass];
        $customAttributes = [
            "title" => trans("Edit")
        ];
        if ($modal) {
            $customAttributes = [
                "title" => "Edit",
                "data-toggle" => "modal",
                "data-bs-target" => !isset($dataBsTarget) ? "#editModal" : $dataBsTarget,
                "data-bs-toggle" => "modal",
                "id" => $id,
                "onclick" => $onClick,
                'data-types' => $data_types,
            ];
        }
        $iconClass = isset($iconClass) ? $iconClass : 'fa fa-edit edit_icon';
        return self::button($iconClass, $url, $customClass, $customAttributes, $iconText);
    }

    /**
     * @param $url
     * @return string
     */
    public static function deleteButton($url, $id = null, $dataId = null, $dataCategory = null, $onclick = true, $customClass = null)
    {
        $customClass = ["btn icon btn-danger delete_btn btn-sm rounded-pill " . $customClass];
        $customAttributes = [
            "title" => trans("Delete"),
            "onclick" => $onclick ? "return confirmationDelete(event);" : '',
            "id" => $id,
            "data-id" => $dataId,
            "data-category" => $dataCategory


        ];
        $iconClass = "fa fa-trash delete_icon";
        return self::button($iconClass, $url, $customClass, $customAttributes);
    }

    /**
     * @param $url
     * @param string $title
     * @return string
     */
    public static function restoreButton($url, string $title = "Restore")
    {
        $customClass = ["btn-gradient-success", "restore-data"];
        $customAttributes = [
            "title" => trans($title),
        ];
        $iconClass = "fa fa-refresh";
        return self::button($iconClass, $url, $customClass, $customAttributes);
    }

    /**
     * @param $url
     * @return string
     */
    public static function trashButton($url)
    {
        $customClass = ["btn-gradient-danger", "trash-data"];
        $customAttributes = [
            "title" => trans("Delete Permanent"),
        ];
        $iconClass = "fa fa-times";
        return self::button($iconClass, $url, $customClass, $customAttributes);
    }


    /**
     * @param $url
     * @return string
     */
    public static function viewRelatedDataButton($url)
    {
        $customClass = ["related-data-form", "btn-inverse-primary"];
        $customAttributes = [
            "title" => trans("View Related Data"),
        ];
        $iconClass = "fa fa-eye";
        return self::button($iconClass, $url, $customClass, $customAttributes);
    }

    public static function optionButton($url)
    {
        $customClass = ["btn-option"];
        $customAttributes = [
            "title" => trans("View Option Data"),
        ];
        $iconClass = "bi bi-gear";
        $iconText = " Options";
        return self::button($iconClass, $url, $customClass, $customAttributes, $iconText);
    }


    public static function deleteAjaxButton($url)
    {
        $customClass = ["delete-form", "btn icon btn-danger delete_btn btn-sm rounded-pill"];
        $customAttributes = [
            "title" => trans("Delete"),
        ];
        $iconClass = "fa fa-trash delete_icon";
        return self::button($iconClass, $url, $customClass, $customAttributes);
    }
}
