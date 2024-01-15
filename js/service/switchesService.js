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

  findSwitchPublic(public_uuid) {
    return $.get(AppConfig.backendServer+'/rest/switch/public/' + public_uuid);
  }

  findSwitchPrivate(private_uuid) {
    return $.get(AppConfig.backendServer+'/rest/switch/private/' + private_uuid);
  }

  modifySubscriptionSwitch(subscription) {
    return $.ajax({
      url: AppConfig.backendServer + '/rest/switch/subscriber',
      method: 'PUT',
      data: JSON.stringify(subscription),
      contentType: 'application/json'
    });
  }

  isSubscribedSwitch(public_uuid, user_name) {
    return $.get(AppConfig.backendServer+'/rest/switch/' + public_uuid +'/subscriber/'+ user_name);
  }

  getNumSubscribersSwitch(public_uuid) {
    return $.get(AppConfig.backendServer+'/rest/switch/' + public_uuid +'/numSubscribers');
  }

  changeSwitchStatePublic(public_uuid, minutes_on){
    return $.ajax({
      url: AppConfig.backendServer + '/rest/switch/public',
      method: 'POST',
      data: JSON.stringify({
        switch_public_uuid: public_uuid,
        minutes_on: minutes_on
      }),
      contentType: 'application/json'
    });
  }
}
