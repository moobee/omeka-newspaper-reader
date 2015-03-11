<?php

if(isset($error))echo json_encode($error);
if(isset($filesTypes))echo json_encode($filesTypes).'&';
if(isset($files))echo json_encode($files);

?>