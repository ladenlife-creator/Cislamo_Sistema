angular.module('erpCislamoApp')
    .service('UserService', ['ApiService', function(ApiService) {
        this.getAll = function() {
            return ApiService.get('/users');
        };
        
        this.getById = function(id) {
            return ApiService.get('/users/' + id);
        };
        
        this.create = function(user) {
            return ApiService.post('/users', user);
        };
        
        this.update = function(id, user) {
            return ApiService.put('/users/' + id, user);
        };
        
        this.delete = function(id) {
            return ApiService.delete('/users/' + id);
        };
    }]);

