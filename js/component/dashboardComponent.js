class DashboardComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.dashboard, switchesModel, null, null);
    
    
    this.switchesModel = switchesModel;
    this.userModel = userModel;
    this.addModel('user', userModel);
    this.router = router;

    this.switchesService = new SwitchesService();

  }

  onStart() {

    this.updateSwitches();
    this.updateSubscribedSwitches();
  }

  updateSwitches() {
    this.switchesService.findAllSwitches(window.sessionStorage.getItem("user_name")).then((data) => {

      this.switchesModel.setSwitches(
        // create a Fronty.Model for each item retrieved from the backend
        data.map(
          (item) => new SwitchModel(item.switch_public_uuid,
            item.switch_private_uuid,
            "TODO owner",
            item.switch_name,
            item.switch_description,
            item.switch_last_power_on,
            item.switch_power_off)
      ));
    });
  }

  updateSubscribedSwitches() {
    this.switchesService.findAllSubscribedSwitches(window.sessionStorage.getItem("user_name")).then((data) => {

      this.switchesModel.setSubscribedSwitches(
        // create a Fronty.Model for each item retrieved from the backend
        data.map(
          (item) => new SwitchModel(item.switch_public_uuid,
            item.switch_private_uuid,
            "TODO owner",
            item.switch_name,
            item.switch_description,
            item.switch_last_power_on,
            item.switch_power_off)
        ));
    });
  }

  // Override
  createChildModelComponent(className, element, id, modelItem) {
    return new PostRowComponent(modelItem, this.userModel, this.router, this);
  }
}

class PostRowComponent extends Fronty.ModelComponent {
  constructor(postModel, userModel, router, postsComponent) {
    super(Handlebars.templates.postrow, postModel, null, null);
    
    this.postsComponent = postsComponent;
    
    this.userModel = userModel;
    this.addModel('user', userModel); // a secondary model
    
    this.router = router;

    this.addEventListener('click', '.remove-button', (event) => {
      if (confirm(I18n.translate('Are you sure?'))) {
        var postId = event.target.getAttribute('item');
        this.postsComponent.postsService.deleteSwitch(postId)
          .fail(() => {
            alert('post cannot be deleted')
          })
          .always(() => {
            this.postsComponent.updatePosts();
          });
      }
    });

    this.addEventListener('click', '.edit-button', (event) => {
      var postId = event.target.getAttribute('item');
      this.router.goToPage('edit-post?id=' + postId);
    });
  }

}
