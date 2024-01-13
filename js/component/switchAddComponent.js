class SwitchAddComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.switchadd, switchesModel, null, null);
    this.switchesModel = switchesModel; // posts
    this.userModel = userModel; // global
    this.addModel('user', userModel);
    this.router = router;

    this.postsService = new SwitchesService();

    // Si se pulsa el botón "Crear", se envía la petición a back y se vuelve al dashboard
    this.addEventListener('click', '#create', () => {
      var newSwitch = {};
      newSwitch.switch_name = $('#switch_name').val();
      newSwitch.switch_description = $('#switch_description').val();
      newSwitch.owner_name = this.userModel.currentUser;
      this.postsService.addSwitch(newSwitch)
        .then(() => {
          this.router.goToPage('dashboard');
          // TODO mensaje de éxito
        })
        .fail((xhr, errorThrown, statusText) => {
          if (xhr.status !== 400) {
            this.switchesModel.set(() => {
              this.switchesModel.errors = xhr.responseJSON;
            });
          } else {
            alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
          }
        });
    });

    // Si se pulsa el botón cancelar, se vuelve a dashboard
    this.addEventListener('click', '#cancel', () => {
      //TODO posible mensaje indicando que se va a perder la información que se ha introducido
      this.router.goToPage('dashboard');
    });
  }

  onStart() {

    // si no hay una sesión activa, reenviamos a login
    this.userService.loginWithSessionData()
      .then((logged) => {
        if (logged != null) {
          this.userModel.setLoggeduser(logged);
        }else{
          this.userModel.logout();
          this.router.goToPage('login');
        }
      });

    this.switchesModel.setSelectedSwitch(new SwitchModel());
  }
}
