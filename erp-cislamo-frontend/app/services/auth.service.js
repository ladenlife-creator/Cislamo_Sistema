angular.module('erpCislamoApp')
    .service('AuthService', ['$http', '$window', function($http, $window) {
        var API_URL = 'http://localhost:8000/api';
        
        this.login = function(credentials) {
            return $http.post(API_URL + '/login', credentials)
                .then(function(response) {
                    $window.localStorage.setItem('token', response.data.access_token);
                    $window.localStorage.setItem('user', JSON.stringify(response.data.user));
                    return response.data;
                });
        };
        
        this.register = function(userData) {
            return $http.post(API_URL + '/register', userData)
                .then(function(response) {
                    $window.localStorage.setItem('token', response.data.access_token);
                    $window.localStorage.setItem('user', JSON.stringify(response.data.user));
                    return response.data;
                });
        };
        
        this.logout = function() {
            var token = this.getToken();
            if (token) {
                return $http.post(API_URL + '/logout', {}, {
                    headers: { 'Authorization': 'Bearer ' + token }
                }).finally(function() {
                    $window.localStorage.removeItem('token');
                    $window.localStorage.removeItem('user');
                });
            } else {
                $window.localStorage.removeItem('token');
                $window.localStorage.removeItem('user');
            }
        };
        
        this.isAuthenticated = function() {
            return !!$window.localStorage.getItem('token');
        };
        
        this.getToken = function() {
            return $window.localStorage.getItem('token');
        };
        
        this.getUser = function() {
            var user = $window.localStorage.getItem('user');
            return user ? JSON.parse(user) : null;
        };
    }]);

