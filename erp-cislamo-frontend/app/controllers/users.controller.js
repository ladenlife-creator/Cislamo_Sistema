angular.module('erpCislamoApp')
    .controller('UsersController', ['$scope', '$location', 'AuthService', 'UserService', function($scope, $location, AuthService, UserService) {
        $scope.users = [];
        $scope.loading = true;
        $scope.showForm = false;
        $scope.currentUser = AuthService.getUser();
        
        $scope.newUser = {
            name: '',
            email: '',
            password: ''
        };
        
        $scope.loadUsers = function() {
            UserService.getAll()
                .then(function(response) {
                    $scope.users = response.data;
                })
                .catch(function(error) {
                    console.error('Erro ao carregar utilizadores:', error);
                })
                .finally(function() {
                    $scope.loading = false;
                });
        };
        
        $scope.toggleForm = function() {
            $scope.showForm = !$scope.showForm;
            if (!$scope.showForm) {
                $scope.resetForm();
            }
        };
        
        $scope.createUser = function() {
            if ($scope.newUser.name && $scope.newUser.email && $scope.newUser.password) {
                UserService.create($scope.newUser)
                    .then(function(response) {
                        $scope.users.unshift(response.data);
                        $scope.toggleForm();
                        alert('Utilizador criado com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao criar utilizador.');
                    });
            }
        };
        
        $scope.deleteUser = function(userId) {
            if (confirm('Tem certeza que deseja remover este utilizador?')) {
                UserService.delete(userId)
                    .then(function() {
                        $scope.users = $scope.users.filter(function(u) { return u.id !== userId; });
                        alert('Utilizador removido com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao remover utilizador.');
                    });
            }
        };
        
        $scope.resetForm = function() {
            $scope.newUser = {
                name: '',
                email: '',
                password: ''
            };
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.goTo = function(path) {
            $location.path(path);
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.loadUsers();
    }]);

