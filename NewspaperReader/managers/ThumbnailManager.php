<?php
/**
 * @author moobee (Dorian MARCHAL)
 * @version 1.1
 * @date 19/01/2013
 */
class ThumbnailManager {
  
    /** Crée une miniature d'une image GIF, PNG ou JPEG

        @param $image_path Chemin vers l'image à redimensionner
        @param $thumbnail_path Chemin vers la miniature
        @param $options Tableau d'options
            max_width Largeur max de l'image
            max_height Hauteur max de l'image
                Note : Il est possible de ne renseigner qu'une seule des deux valeurs 
            type (défaut = "jpg") Format de l'image "jpg" ou "png"
            quality (défaut = "90") Qualité de l'image, pris en compte quand type = "jpg"
            fill_type (défaut = "scale") Technique utilisée pour 'remplir' la miniature (voir article "Création de miniatures" dans la base de connaissances)
                scale  : Ne force pas la miniature à prendre les dimensions passés en paramètre
                stretch : Etire la miniature pour la forcer à prendre les dimensions passées en paramètre (peut déformer l'image)
                crop    : zoom sur l'image pour forcer la miniature à prendre les dimensions passées en paramètre (le zoom est fait au centre de l'image)
        @param $image_infos (optionnel, défaut =  null) Tableau qui contiendra les infos de la vignette ($infos['width'] et $infos['weight'])

        @return true si la fonction s'est exécutée sans problème

        @author moobee (Dorian MARCHAL)
    */
    public function create_thumbnail($image_path, $thumbnail_path, $options, &$image_infos = null) {

        if(is_null($options)) {
            throw new Exception('Un tableau d\'options doit être passé en paramètre de la fonction create_thumbnail.');
            return false;
        }

        extract($options);

        if(!isset($max_width) && !isset($max_height)) {
            throw new Exception('Une des valeurs $options[\'max_width\'] ou $options[\'max_height\'] doit être renseigné au minimumum.');
            return false;
        }

        //Attribution des paramètres par défaut
        $type = isset($type) ? $type : "jpg";
        $quality = isset($quality) ? $quality : 90;
        $fill_type = isset($fill_type) ? $fill_type : 'scale';

        if(!in_array($type, array('jpg', 'png'))) {
            throw new Exception('$options[\'type\'] ne peut prendre qu\'une des valeurs suivantes : "jpg", "png".');
            return false;
        }

        if(!in_array($fill_type, array('scale', 'stretch', 'crop'))) {
            throw new Exception('$options[\'fill_type\'] ne peut prendre qu\'une des valeurs suivantes : "scale", "stretch" ou "crop".');
            return false;
        }

        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($image_path);
        
        $source_gd_image = false;

        try{
            switch ($source_image_type) {
                case IMAGETYPE_GIF:
                    $source_gd_image = imagecreatefromgif($image_path);
                    break;
                case IMAGETYPE_JPEG:
                    $source_gd_image = imagecreatefromjpeg($image_path);
                    break;
                case IMAGETYPE_PNG:
                    $source_gd_image = imagecreatefrompng($image_path);
                    break;
            }
        }catch(Exception $e){
            throw new Exception('Le fichier $image_path est endommagé');
            return false;
        }

        if ($source_gd_image === false) {
            return false;
        }

        if(!isset($max_height)) {
            $max_height = $source_image_height;
        }
        else if(!isset($max_width)) {
            $max_width = $source_image_width;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;
        
        $thumbnail_aspect_ratio = $max_width / $max_height;
        
        if($fill_type == 'stretch') {
            $thumbnail_image_width = $max_width;
            $thumbnail_image_height = $max_height;
        }
        else if($fill_type == 'scale') {
            //Si l'image est plus petite que la miniature, on ne la redimensionne pas.
            if ($source_image_width <= $max_width && $source_image_height <= $max_height) {
                $thumbnail_image_width = $source_image_width;
                $thumbnail_image_height = $source_image_height;
            }
            //Si la miniature a un plus grand ratio (plus en largeur)
            else if ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                $thumbnail_image_width = (int) ($max_height * $source_aspect_ratio);
                $thumbnail_image_height = $max_height;
            }
            //Si la miniature a un plus petit ratio (plus en hauteur)
            else {
                $thumbnail_image_width = $max_width;
                $thumbnail_image_height = (int) ($max_width / $source_aspect_ratio);
            }
        }
        else if($fill_type == 'crop') {
            //Si la miniature a un plus grand ratio (plus en largeur)
            if ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                $thumbnail_image_width = $max_width;
                $thumbnail_image_height = (int) ($max_width / $source_aspect_ratio);
            }
            //Si la miniature a un plus petit ratio (plus en hauteur)
            else {
                $thumbnail_image_width = (int) ($max_height * $source_aspect_ratio);
                $thumbnail_image_height = $max_height;
            }
        }

        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagealphablending($thumbnail_gd_image, false);
        imagesavealpha($thumbnail_gd_image, true);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);

        if($fill_type == 'crop') {

            $real_thumbnail_gd_image = imagecreatetruecolor($max_width, $max_height);
            imagealphablending($real_thumbnail_gd_image, false);
            imagesavealpha($real_thumbnail_gd_image, true);

            //On calcule le point de départ du "crop" de l'image
            $x_offset = 0;
            $y_offset = 0;

            if($thumbnail_image_width > $max_width) {
                $x_offset = ($thumbnail_image_width - $max_width) / 2;
            }
            else if($thumbnail_image_height > $max_height) {
                // $y_offset = ($thumbnail_image_height - $max_height) / 2;
                $y_offset = 0;
            }

            imagecopyresampled($real_thumbnail_gd_image, $thumbnail_gd_image, 0, 0, $x_offset, $y_offset, $max_width, $max_height, $max_width, $max_height);
            $thumbnail_gd_image = $real_thumbnail_gd_image;
        }

        switch($type) {
            case "jpg" :
                imagejpeg($thumbnail_gd_image, $thumbnail_path, $quality);
                break;
            case "png" :
                imagepng($thumbnail_gd_image, $thumbnail_path);
                break;
            default:
                return false;
        }

        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        if(!is_null($image_infos)) {
            $image_infos['width'] = $thumbnail_image_width;
            $image_infos['height'] = $thumbnail_image_height;
        }
        
        return true;
    }

}
?>