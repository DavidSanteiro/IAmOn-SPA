class UserModel extends Fronty.Model {
  constructor() {
    super('UserModel');
    this.isLogged = false;
  }

  setLoggeduser(loggedUser) {
    this.set((self) => {
      self.currentUser = loggedUser;
      self.isLogged = true;
    });
  }

  logout() {
    this.set((self) => {
      delete self.currentUser;
      self.isLogged = false;
    });
  }

  startRecoveryProtocol(){
    this.set((self) => {
      self.startedRecoveryProtocol = true;
    });
  }

  finishRecoveryProtocol(){
    this.set((self) => {
      self.startedRecoveryProtocol = false;
    });
  }
}
