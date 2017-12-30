<?php

print('test<br>');

if($_FILES['file']){
  print_r($_FILES['file']);
  if($_FILES['file']['type'] == 'image/png'){
    if($_FILES['file']['size'] < 500000){
      #move_uploaded_file($_FILES['file']['tmp_name'], './img/00.png');
      #print('loaded.<br>');
      print('PNG is denied');
    }else{
      print('too big.<br>');
    }
  }else if($_FILES['file']['type'] == 'image/jpeg'){
    if($_FILES['file']['size'] < 300000){
      move_uploaded_file($_FILES['file']['tmp_name'], './img/00.jpg');
      print('loaded.<br>');
    }else{
      print('too big.<br>');
    }
  }else{
    print('not PNG or JPG.<br>');
  }
}

?>
