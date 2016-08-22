<?php
  //Массив для лайкнувших не друзей
  $likes=array();

  //Друзья
  //friends.get
  $friend_request_params = array(
    'user_id' => $user_id,
  );
  $friend=vk_get('friends.get', $friend_request_params);
  $friends=array();
  if(isset($friend->response->items)) {
    foreach($friend->response->items as $friend_item) {
      $friends[$friend_item]=$friend_item;
    }
  }

  //Стена
  //wall.get
  $offset=0;
  $wall_count=101;

  while($offset<$wall_count) {
    $wall_request_params = array(
      'owner_id' => $user_id,
      'offset' => $offset,
      'count' => 100,
    );

    $wall=vk_get('wall.get', $wall_request_params);
    $offset=$offset+100;

    if(isset($wall->response->count)) {
      $wall_count=$wall->response->count;
    }

    if(isset($wall->response->items)) {
      foreach($wall->response->items as $wall_item) {
        //Лайки на стене
        $like=vk_get_like($type='post', $user_id, $wall_item->id);
        if(isset($like)) {
          foreach($like as $like_item) {
            if(!isset($friends[$like_item])) {
              $likes[$like_item]='https://vk.com/id'.$like_item;
            }
          }
        }
      }
    }
    else {
      break;
    }
  }

  //Альбомы
  $album_request_params = array(
    'owner_id' => $user_id,
    'need_system' => 1,
  );
  $album=vk_get('photos.getAlbums', $album_request_params);
  if(isset($album->response->items)) {
    foreach($album->response->items as $album_item) {
      //Фото
      $offset=0;
      $photo_count=101;

      while($offset<$photo_count) {
        $photo_request_params = array(
          'owner_id' => $user_id,
          'album_id' =>$album_item->id,
          'offset' => $offset,
          'count' => 100,
        );
        $photo=vk_get('photos.get', $photo_request_params);
        $offset=$offset+100;
        if(isset($photo->response->count)) {
          $photo_count=$photo->response->count;
        }
        if(isset($photo->response->items)) {
          foreach($photo->response->items as $photo_item) {
            //Лайки на стене
            $like=vk_get_like($type='photo', $user_id, $photo_item->id);
            if(isset($like)) {
              foreach($like as $like_item) {
                if(!isset($friends[$like_item])) {
                  $likes[$like_item]='https://vk.com/id'.$like_item;
                }
              }
            }
          }
        }
      }
    }
  }

  foreach($likes as $row) {
    print '<a href="'.$row.'" target="_blank">'.$row.'</a><br>';
  }

function vk_get($method, $request_params) {
  //Общие настройки
  $v='5.53';
  $api_url='http://api.vk.com/method/';
  //Запрос
  $request_params['v']=$v;
  $get_params = http_build_query($request_params);
  $json=file_get_contents($api_url.$method.'?'. $get_params);
  return json_decode($json);
}

function vk_get_like($type='post', $user_id, $item_id) {
  $likes=array();
  //Лайки
  $like_request_params = array(
    'owner_id' => $user_id,
    'item_id' => $item_id,
    'type'=> $type,
  );
  $like=vk_get('likes.getList', $like_request_params);
  if(isset($like->response->items)) {
    foreach($like->response->items as $like_item) {
      $likes[$like_item]=$like_item;
    }
  }
  return $likes;
}
