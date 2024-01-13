class SwitchViewComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.switchview, switchesModel);

    this.switchesModel = switchesModel;
    this.userModel = userModel;
    this.addModel('user', userModel);
    this.router = router;

    this.switchesService = new SwitchesService();

    // Listener para encender/apagar el switch
    this.addEventListener('onchange', '#changeSwitchState', (event) => {
      alert("SE LLAMO A CAMBIAR ESTADO A " + event.target.selected);
      // var selectedId = this.router.getRouteQueryParam('id');
      // this.switchesService.changeSwitchStatePublic(selectedId, {
      //     content: $('#commentcontent').val()
      //   })
      //   .then(() => {
      //     $('#commentcontent').val('');
      //     this.loadPost(selectedId);
      //   })
      //   .fail((xhr, errorThrown, statusText) => {
      //     if (xhr.status == 400) {
      //       this.switchesModel.set(() => {
      //         this.switchesModel.commentErrors = xhr.responseJSON;
      //       });
      //     } else {
      //       alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
      //     }
      //   });
    });

    // Listener para editar el switch
    this.addEventListener('click', '#edit_button', (event) => {
      var public_uuid = event.target.getAttribute("data-public-uuid");
      this.router.goToPage(["edit-switch?public_uuid="+public_uuid]);
    });

    // Listener para suscribirse/desuscribirse al switch
    this.addEventListener('click', '#changeSubscriptionStateImg', () => {
      this.switchesModel.selectedSwitch
      let user_name = window.sessionStorage.getItem("user_name");
      if (user_name == null || user_name === "undefined"){
        alert("Necesitas inciar sesión");
        this.router.goToPage('login');
      }else{
        this.switchesService.modifySubscriptionSwitch(
          {
            switch_public_uuid: this.switchesModel.selectedSwitch.public_uuid,
            is_subscribed: !this.switchesModel.selectedSwitch.is_subscribed
          })
          .then(() => {
            $('#commentcontent').val('');
            this.loadPost(selectedId);
          })
          .fail((xhr, errorThrown, statusText) => {
              alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
          });
      }

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
    var current_user = window.sessionStorage.getItem("user_name");

    if (selectedSwitchPrivateUuid != null) {
      this.switchesService.findSwitchPrivate(selectedSwitchPrivateUuid)
        .then((selectedSwitch) => {
          this.switchesModel.setSelectedSwitch(selectedSwitch);

          this.switchesModel.selectedSwitch.hasPermissions = true;
        });

    }else if (selectedSwitchPublicUuid != null){
      this.switchesService.findSwitchPublic(selectedSwitchPublicUuid)
        .then((selectedSwitch) => {
          this.switchesModel.setSelectedSwitch(selectedSwitch);

          this.switchesModel.selectedSwitch.hasPermissions =
            (this.switchesModel.selectedSwitch.owner_name === current_user);
        });

    }else{
      alert("Error: No se ha seleccionado un switch");
    }

    
  }
}
