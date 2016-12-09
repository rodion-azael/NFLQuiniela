exports.config =
  # See http://brunch.io/#documentation for docs.
  files:
    javascripts:
      joinTo:
        'js/app.js': /^(app)/
        'js/vendor.js': /^(vendor|bower_components)/
      order:
        before:[
          'app/scripts/app.coffee',
          'app/scripts/Services/nflService.coffee'          
        ]
        
    stylesheets:
      joinTo:
        'css/app.css' : /^(app)/
        'css/vendor.css': /^(vendor|bower_components)/

  plugins:
    coffeescript:
      bare: true
  modules:
    nameCleaner: (path) ->
      path.replace(/^app\/scripts\//, '')
    wrapper: false
    definition: false
  sourceMaps: false
