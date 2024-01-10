class SwitchesModel extends Fronty.Model {

  constructor() {
      super('SwitchesModel');

      // model attributes
      this.mySwitches = [];
      this.subscribedSwitches = [];
  }

  setSelectedSwitch(selectedSwitch) {
        this.set((self) => {
            self.selectedSwitch = selectedSwitch;
        });
    }

  setSwitches(switches) {
      this.set((self) => {
          self.mySwitches = switches;
      });
  }

  setSubscribedSwitches(subscribedSwitches){
      this.set((self) => {
          self.subscribedSwitches = subscribedSwitches;
      })
  }
}
