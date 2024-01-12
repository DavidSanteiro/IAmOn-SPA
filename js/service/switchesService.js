class SwitchesService {
  constructor() {

  }

  findAllSwitches(user_name) {
    return $.get(AppConfig.backendServer+'/rest/switch/' + user_name);
  }

  findAllSubscribedSwitches(user_name){
    return $.get(AppConfig.backendServer+'/rest/switch/subscription/' + user_name);
  }

  addSwitch(newSwitch) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/switch/new',
      method: 'POST',
      data: JSON.stringify(newSwitch),
      contentType: 'application/json'
    });
  }

  saveSwitch(modifiedSwitch) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/switch',
      method: 'PUT',
      data: JSON.stringify(modifiedSwitch),
      contentType: 'application/json'
    });
  }

  deleteSwitch(deletedSwitch) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/switch',
      method: 'DELETE',
      data: JSON.stringify(deletedSwitch),
      contentType: 'application/json'
    });
  }

  findSwitch(uuid) {
    return $.get(AppConfig.backendServer+'/rest/switch/public/' + uuid);
  }

//TODO ajustar los siguientes métodos de Post a Switch


  // createComment(postid, comment) {
  //   return $.ajax({
  //     url: AppConfig.backendServer+'/rest/post/' + postid + '/comment',
  //     method: 'POST',
  //     data: JSON.stringify(comment),
  //     contentType: 'application/json'
  //   });
  // }

}
