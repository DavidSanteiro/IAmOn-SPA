class SwitchViewComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.switchview, switchesModel);

    this.switchesModel = switchesModel;
    this.userModel = userModel;
    this.addModel('user', userModel);
    this.router = router;

    this.userService = new UserService();
    this.switchesService = new SwitchesService();

    // Listener para encender/apagar el switch
    this.addEventListener('change', '#changeSwitchState', (event) => {
      let minutes = 0;
      if (event.target.checked){
        minutes = $('#time_on').val();
      }
      this.switchesService.changeSwitchStatePublic(this.switchesModel.selectedSwitch.public_uuid, minutes)
        .then((data) => {
          let updatedSwitch = this.switchesModel.selectedSwitch;
          updatedSwitch.setLast_power_on(data.switch_last_power_on);
          updatedSwitch.setPower_off(data.switch_power_off);
          this.switchesModel.setSelectedSwitch(updatedSwitch);
        });
    });

    // Listener para editar el switch
    this.addEventListener('click', '#edit_button', (event) => {
      var public_uuid = event.target.getAttribute("data-public-uuid");
      this.router.goToPage(["edit-switch?public_uuid="+public_uuid]);
    });

    // Listener para suscribirse/desuscribirse al switch
    this.addEventListener('click', '#changeSubscriptionStateImg', () => {
      this.switchesService.modifySubscriptionSwitch(
        {
          switch_public_uuid: this.switchesModel.selectedSwitch.public_uuid,
          is_subscribed: this.switchesModel.selectedSwitch.is_subscribed
        })
        .then(() => {
          let updatedSwitch = this.switchesModel.selectedSwitch;
          updatedSwitch.is_subscribed = !updatedSwitch.is_subscribed;

          // Actualizamos también el número de suscriptores para reflejar la nueva suscripción
          this.switchesService.getNumSubscribersSwitch(updatedSwitch.public_uuid)
            .then((data) => {
              updatedSwitch.setNumSubscribers(data.num_subscriptions);
              this.switchesModel.setSelectedSwitch(updatedSwitch);
            });
        })
        .catch((xhr, errorThrown, statusText) => {
          alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
        });
      });

    this.addEventListener('click', '#submitSubscription', () => {
      this.switchesService.modifySubscriptionSwitch(
        {
          switch_public_uuid: this.switchesModel.selectedSwitch.public_uuid,
          is_subscribed: this.switchesModel.selectedSwitch.is_subscribed
        })
        .then(() => {
          let updatedSwitch = this.switchesModel.selectedSwitch;
          updatedSwitch.is_subscribed = !updatedSwitch.is_subscribed;

          // Actualizamos también el número de suscriptores para reflejar la nueva suscripción
          this.switchesService.getNumSubscribersSwitch(updatedSwitch.public_uuid)
            .then((data) => {
              updatedSwitch.setNumSubscribers(data.num_subscriptions);
              this.switchesModel.setSelectedSwitch(updatedSwitch);
            });
        })
        .catch((xhr, errorThrown, statusText) => {
          alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
        });
      });


    // this.addEventListener('click', '#savecommentbutton', () => {
    //   var selectedId = this.router.getRouteQueryParam('id');
    //   this.switchesService.createComment(selectedId, {
    //       content: $('#commentcontent').val()
    //     })
    //     .then(() => {
    //       $('#commentcontent').val('');
    //       this.loadPost(selectedId);
    //     })
    //     .fail((xhr, errorThrown, statusText) => {
    //       if (xhr.status == 400) {
    //         this.switchesModel.set(() => {
    //           this.switchesModel.commentErrors = xhr.responseJSON;
    //         });
    //       } else {
    //         alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
    //       }
    //     });
    // });
  }

  onStart() {
    var selectedSwitchPrivateUuid = this.router.getRouteQueryParam('private_uuid');
    var selectedSwitchPublicUuid = this.router.getRouteQueryParam('public_uuid');

    // Intentamos iniciar sesión con la información contenida en session storage
    this.userService.loginWithSessionData()
      .then(logged => {
        if (logged != null) {
          this.userModel.setLoggeduser(logged);
        }else{
          this.userModel.logout();
        }

        // Obtenemos la información del switch
        if (selectedSwitchPrivateUuid != null) {
          this.switchesService.findSwitchPrivate(selectedSwitchPrivateUuid)
            .then((data) => {
              // crear un objeto switch con los datos recibidos del back
              let switchModel = new SwitchModel(
                data.switch_public_uuid,
                data.switch_private_uuid,
                data.owner_name,
                data.switch_name,
                data.switch_description,
                data.switch_last_power_on,
                data.switch_power_off);

              switchModel.setHasPermissions(true);

              // Obtenemos el número de suscriptores al switch
              this.switchesService.getNumSubscribersSwitch(switchModel.public_uuid)
                .then((data) => {
                  switchModel.setNumSubscribers(data.num_subscriptions);

                  // Si está loggeado, obtenemos el estado de la suscripción
                  if (this.userModel.logged != null){
                    this.switchesService.isSubscribedSwitch(switchModel.public_uuid, this.userModel.currentUser)
                      .then((data) => {
                        switchModel.setIsSubscribed(data.is_subscribed);
                        this.switchesModel.setSelectedSwitch(switchModel);
                      });
                  }else{
                    this.switchesModel.setSelectedSwitch(switchModel);
                  }
                });
            });

        }else if (selectedSwitchPublicUuid != null){
          this.switchesService.findSwitchPublic(selectedSwitchPublicUuid)
            .then((data) => {
              // crear un objeto switch con los datos recibidos del back
              let switchModel = new SwitchModel(
                data.switch_public_uuid,
                data.switch_private_uuid,
                data.owner_name,
                data.switch_name,
                data.switch_description,
                data.switch_last_power_on,
                data.switch_power_off);

              switchModel.setHasPermissions(switchModel.owner_name === this.userModel.currentUser);
              // Obtenemos el número de suscriptores al switch
              this.switchesService.getNumSubscribersSwitch(switchModel.public_uuid)
                .then((data) => {
                  switchModel.setNumSubscribers(data.num_subscriptions);

                  // Si ha iniciado sesión, obtenemos el estado de la suscripción
                  if (this.userModel.currentUser != null){
                    this.switchesService.isSubscribedSwitch(switchModel.public_uuid, this.userModel.currentUser)
                      .then((data) => {
                        switchModel.setIsSubscribed(data.is_subscribed);
                        this.switchesModel.setSelectedSwitch(switchModel);
                      });
                  }else{
                    this.switchesModel.setSelectedSwitch(switchModel);
                  }
                });
            });
        }else{
          alert("Error: No se ha encontrado un switch. Asegúrate de que la URL sea correcta");
        }
      });
  }
}
