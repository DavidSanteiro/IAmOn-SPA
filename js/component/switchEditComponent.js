class SwitchEditComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.switchedit, switchesModel);
    this.switchesModel = switchesModel; // switches
    this.userModel = userModel; // global
    this.addModel('user', userModel);
    this.router = router;

    this.switchesService = new SwitchesService();

    // Si se pulsa el botón "Modificar", se envía la petición a back y se vuelve al dashboard
    this.addEventListener('click', '#modify', () => {
      this.switchesModel.selectedSwitch.switch_name = $('#switch_name').val();
      this.switchesModel.selectedSwitch.switch_description = $('#switch_description').val();
      this.switchesModel.selectedSwitch.reset_switch_private_uuid = $('#reset_url').val();
      this.switchesService.saveSwitch(this.switchesModel.selectedSwitch)
        .then(() => {
          this.switchesModel.set((model) => {
            model.errors = []
          });
          this.router.goToPage('dashboard');
          //TODO: mensaje de éxito
        })
        .fail((xhr, errorThrown, statusText) => {
          if (xhr.status == 400) {
            this.switchesModel.set((model) => {
              model.errors = xhr.responseJSON;
            });
          } else {
            alert('an error has occurred during request: ' + statusText + '.' + xhr.responseText);
          }
        });
    });

    // Si se pulsa el botón "Eliminar", se envía la petición a back y se vuelve al dashboard
    this.addEventListener('click', '#delete', () => {
      this.switchesService.deleteSwitch(this.switchesModel.selectedSwitch)
        .then(() => {
          this.switchesModel.set((model) => {
            model.errors = []
          });
          this.router.goToPage('dashboard');
          //TODO: mensaje de éxito
        })
        .fail((xhr, errorThrown, statusText) => {
          if (xhr.status == 400) {
            this.switchesModel.set((model) => {
              model.errors = xhr.responseJSON;
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
    var selectedSwitchUuid = this.router.getRouteQueryParam('public_uuid');
    if (selectedSwitchUuid != null) {
      this.switchesService.findSwitch(selectedSwitchUuid)
        .then((selectedSwitch) => {
          this.switchesModel.setSelectedSwitch(selectedSwitch);
        });
    }else{
      alert("Error: No se ha seleccionado un switch");
      this.router.goToPage('dashboard');
    }
  }
}
