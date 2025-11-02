angular.module('erpCislamoApp')
    .service('EventService', ['ApiService', function(ApiService) {
        this.getAll = function() {
            return ApiService.get('/events');
        };
        
        this.getById = function(id) {
            return ApiService.get('/events/' + id);
        };
        
        this.create = function(event) {
            return ApiService.post('/events', event);
        };
        
        this.update = function(id, event) {
            return ApiService.put('/events/' + id, event);
        };
        
        this.delete = function(id) {
            return ApiService.delete('/events/' + id);
        };
    }]);

