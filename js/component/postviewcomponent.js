class PostViewComponent extends Fronty.ModelComponent {
  constructor(postsModel, userModel, router) {
    super(Handlebars.templates.postview, postsModel);

    this.postsModel = postsModel; // posts
    this.userModel = userModel; // global
    this.addModel('user', userModel);
    this.router = router;

    this.postsService = new SwitchesService();

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
    var selectedId = this.router.getRouteQueryParam('id');
    this.loadPost(selectedId);
  }

  loadPost(postId) {
    if (postId != null) {
      this.postsService.findSwitch(postId)
        .then((post) => {
          this.postsModel.setSelectedPost(post);
        });
    }
  }
}
