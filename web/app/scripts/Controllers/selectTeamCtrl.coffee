selectTeamCtrl = ($uibModalInstance, items) ->
  vm = @

  init = () ->
    vm.Teams = items
    vm.SelectedConference = 'NFC'
    return

  pickTeam = (id) ->
    $uibModalInstance.close(id);
    return

  # ----- #
  changeConference = (conference) ->
    vm.SelectedConference = conference
    return

  vm.Teams = []

  vm.changeConference = changeConference
  vm.pickTeam = pickTeam

  do init
  return


angular
  .module('nfl')
  .controller('selectTeamCtrl', selectTeamCtrl)