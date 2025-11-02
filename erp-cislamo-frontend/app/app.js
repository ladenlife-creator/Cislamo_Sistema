angular.module('erpCislamoApp', ['ngRoute'])
    .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'views/home.html',
                controller: 'HomeController'
            })
            .when('/login', {
                templateUrl: 'views/login.html',
                controller: 'LoginController'
            })
            .when('/dashboard', {
                templateUrl: 'views/dashboard.html',
                controller: 'DashboardController',
                requireAuth: true
            })
            .when('/users', {
                templateUrl: 'views/users.html',
                controller: 'UsersController',
                requireAuth: true
            })
            .when('/documents', {
                templateUrl: 'views/documents.html',
                controller: 'DocumentsController',
                requireAuth: true
            })
            .when('/events', {
                templateUrl: 'views/events.html',
                controller: 'EventsController',
                requireAuth: true
            })
            .otherwise({
                redirectTo: '/'
            });
    }])
    .run(['$rootScope', '$location', 'AuthService', function($rootScope, $location, AuthService) {
        $rootScope.$on('$routeChangeStart', function(event, next) {
            if (next.requireAuth && !AuthService.isAuthenticated()) {
                event.preventDefault();
                $location.path('/login');
            }
        });
    }]);

