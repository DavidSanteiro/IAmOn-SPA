class DashboardComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.dashboard, switchesModel, null, null);


    this.switchesModel = switchesModel;
    this.userModel = userModel;
    this.addModel('user', userModel);
    this.router = router;

    this.switchesService = new SwitchesService();
    this.userService = new UserService();

    // Si se pulsa el botón "Añadir", se va a la página de nuevo switch
    this.addEventListener('click', '#new_switch', () => {
      this.router.goToPage(["add-switch"]);
    });

    this.addEventListener('click', '.edit', (event) => {
      var public_uuid = event.target.getAttribute("data-public-uuid");
      this.router.goToPage(["edit-switch?public_uuid="+public_uuid]);
    });

    this.addEventListener('click', '.view', (event) => {
      var public_uuid = event.target.getAttribute("data-public-uuid");
      this.router.goToPage(["view-switch?public_uuid="+public_uuid]);
    });

    this.addEventListener('click', '#changeSubscriptionStateImgDashboard', (event) => {
      var public_uuid = event.target.getAttribute("data-public-uuid");

      this.switchesService.modifySubscriptionSwitch(
        {
          switch_public_uuid: public_uuid,
          is_subscribed: true
        })
        .then(() => {

          // Encuentra el índice del objeto en el array
          let indice = this.switchesModel.subscribedSwitches.findIndex(
            unsubscribedSwitch => unsubscribedSwitch.public_uuid === public_uuid);
          // Si el objeto se encontró en el array
          if (indice !== -1) {
            // Elimina el objeto del array
            var subscribedSwitches = this.switchesModel.subscribedSwitches;
            subscribedSwitches.splice(indice, 1);
            this.switchesModel.setSubscribedSwitches(subscribedSwitches);
          }

        })
        .catch((xhr, errorThrown, statusText) => {
          alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
        });
      });

    this.addEventListener('change', '.changeSwitchState', (event) => {
      var public_uuid = event.target.getAttribute("data-public-uuid");

      let minutes = 0;
      if (event.target.checked){
        minutes = $('#time_on'+public_uuid).val();
      }
      this.switchesService.changeSwitchStatePublic(public_uuid, minutes)
        .then((data) => {
          // Encuentra el índice del objeto en el array
          let indice = this.switchesModel.mySwitches.findIndex(
            changedStateSwitch => changedStateSwitch.public_uuid === public_uuid);
          // Si el objeto se encontró en el array
          if (indice !== -1) {
            // Elimina el objeto del array
            let mySwitches = this.switchesModel.mySwitches;
            mySwitches[indice].setLast_power_on(data.switch_last_power_on);
            mySwitches[indice].setPower_off(data.switch_power_off);
            this.switchesModel.setSwitches(mySwitches);
          }

        });
    });

  }

  onStart() {

    // si no hay una sesión activa, reenviamos a login
    this.userService.loginWithSessionData()
      .then(logged => {
        if (logged != null) {
          this.userModel.setLoggeduser(logged);

          //Acciones para inicializar la página si hay sesión
          this.updateSwitches();
          this.updateSubscribedSwitches();

        }else{
          this.userModel.logout();
          this.router.goToPage('login');
        }
      });
  }

  updateSwitches() {
    this.switchesService.findAllSwitches(window.sessionStorage.getItem("user_name")).then((data) => {

      this.switchesModel.setSwitches(
        // create a Fronty.Model for each item retrieved from the backend
        data.map(
          (item) => new SwitchModel(
            item.switch_public_uuid,
            item.switch_private_uuid,
            item.owner_name,
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
            item.owner_name,
            item.switch_name,
            item.switch_description,
            item.switch_last_power_on,
            item.switch_power_off)
        ));
    });
  }

  // Override
  // createChildModelComponent(className, element, id, modelItem) {
  //   return new PostRowComponent(modelItem, this.userModel, this.router, this);
  // }
}

// class PostRowComponent extends Fronty.ModelComponent {
//   constructor(postModel, userModel, router, postsComponent) {
//     super(Handlebars.templates.postrow, postModel, null, null);
//
//     this.postsComponent = postsComponent;
//
//     this.userModel = userModel;
//     this.addModel('user', userModel); // a secondary model
//
//     this.router = router;
//
//     this.addEventListener('click', '.remove-button', (event) => {
//       if (confirm(I18n.translate('Are you sure?'))) {
//         var postId = event.target.getAttribute('item');
//         this.postsComponent.postsService.deleteSwitch(postId)
//           .fail(() => {
//             alert('post cannot be deleted')
//           })
//           .always(() => {
//             this.postsComponent.updatePosts();
//           });
//       }
//     });
//
//     this.addEventListener('click', '.edit-button', (event) => {
//       var postId = event.target.getAttribute('item');
//       this.router.goToPage('edit-post?id=' + postId);
//     });
//   }
//
// }
