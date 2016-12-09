loginCtrl = ($scope, $localStorage, $state, NFLAPI) ->
  vm = @

  login = () ->
    vm.errorMsg = ''
    params = { 
      user: vm.User
      pass: vm.Pass
    }
    NFLAPI.LogIn(params)
      .then (data) ->
        if data is null
          vm.errorMsg = 'Datos Incorrectos'
        else if data.UserID
          $localStorage.user = data
          $state.go('main.weeks')
        return
    return


  init = () ->
    angular.element '#passInput'
      .keydown (e) ->
        if event.which is 13 then do login
        return
    vm.errorMsg = ''
    return

  vm.login = login

  do init
  return


angular
  .module('nfl')
  .controller('loginCtrl', loginCtrl)
