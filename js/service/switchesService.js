class SwitchesService {
  constructor() {

  }

  findAllSwitches(user_name) {
    return $.get(AppConfig.backendServer+'/rest/switch/' + user_name);
  }

  findAllSubscribedSwitches(user_name){
    return $.get(AppConfig.backendServer+'/rest/switch/subscription/' + user_name);
  }


//TODO ajustar los siguientes métodos de Post a Switch
  findSwitch(uuid) {
    return $.get(AppConfig.backendServer+'/rest/switch/' + uuid);
  }

  deleteSwitch(id) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/post/' + id,
      method: 'DELETE'
    });
  }

  saveSwitch(post) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/post/' + post.id,
      method: 'PUT',
      data: JSON.stringify(post),
      contentType: 'application/json'
    });
  }

  addSwitch(post) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/post',
      method: 'POST',
      data: JSON.stringify(post),
      contentType: 'application/json'
    });
  }

  // createComment(postid, comment) {
  //   return $.ajax({
  //     url: AppConfig.backendServer+'/rest/post/' + postid + '/comment',
  //     method: 'POST',
  //     data: JSON.stringify(comment),
  //     contentType: 'application/json'
  //   });
  // }

}
