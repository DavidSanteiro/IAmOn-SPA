/* Main iamon-front script */

//load external resources
function loadTextFile(url) {
  return new Promise((resolve, reject) => {
    $.get({
      url: url,
      cache: true,
      beforeSend: function( xhr ) {
        xhr.overrideMimeType( "text/plain" );
      }
    }).then((source) => {
      resolve(source);
    }).fail(() => reject());
  });
}


// Configuration
var AppConfig = {
  backendServer: 'http://localhost'
  //backendServer: '/mvcblog'
}

Handlebars.templates = {};
Promise.all([
    I18n.initializeCurrentLanguage('js/i18n'),

    // Layouts
    loadTextFile('templates/layouts/logged_out.hbs').then((source) =>
      Handlebars.templates.loggedOut = Handlebars.compile(source)),
  loadTextFile('templates/layouts/logged_in.hbs').then((source) =>
    Handlebars.templates.loggedIn = Handlebars.compile(source)),


    loadTextFile('templates/components/language.hbs').then((source) =>
      Handlebars.templates.language = Handlebars.compile(source)),
    loadTextFile('templates/components/user.hbs').then((source) =>
      Handlebars.templates.user = Handlebars.compile(source)),

    // User templates
    loadTextFile('templates/components/login.hbs').then((source) =>
      Handlebars.templates.login = Handlebars.compile(source)),
    loadTextFile('templates/components/register.hbs').then((source) =>
      Handlebars.templates.register = Handlebars.compile(source)),

    // Swtiches templates
    loadTextFile('templates/components/dashboard.hbs').then((source) =>
    Handlebars.templates.dashboard = Handlebars.compile(source)),

    loadTextFile('templates/components/post-edit.hbs').then((source) =>
      Handlebars.templates.postedit = Handlebars.compile(source)),
    loadTextFile('templates/components/post-view.hbs').then((source) =>
      Handlebars.templates.postview = Handlebars.compile(source)),
    loadTextFile('templates/components/post-row.hbs').then((source) =>
      Handlebars.templates.postrow = Handlebars.compile(source))
  ])
  .then(() => {
    $(() => {
      new MainComponent().start();
    });
  }).catch((err) => {
    alert('FATAL: could not start app ' + err);
  });
