class PostEditComponent extends Fronty.ModelComponent {
  constructor(switchesModel, userModel, router) {
    super(Handlebars.templates.switchadd, switchesModel);
    this.switchesModel = switchesModel; // switches
    this.userModel = userModel; // global
    this.addModel('user', userModel);
    this.router = router;

    this.switchesService = new SwitchesService();

    this.addEventListener('click', '#savebutton', () => {
      this.switchesModel.selectedPost.title = $('#title').val();
      this.switchesModel.selectedPost.content = $('#content').val();
      this.switchesService.saveSwitch(this.switchesModel.selectedPost)
        .then(() => {
          this.switchesModel.set((model) => {
            model.errors = []
          });
          this.router.goToPage('dashboard');
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
  }

  onStart() {
    var selectedId = this.router.getRouteQueryParam('id');
    if (selectedId != null) {
      this.switchesService.findSwitch(selectedId)
        .then((post) => {
          this.switchesModel.setSelectedPost(post);
        });
    }
  }
}
