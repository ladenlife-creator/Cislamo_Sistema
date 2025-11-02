angular.module('erpCislamoApp')
    .controller('HomeController', ['$scope', '$location', '$document', function($scope, $location, $document) {
        $scope.features = [
            {
                icon: '👥',
                title: 'Gestão de Utilizadores',
                description: 'Controle completo sobre usuários, permissões e acessos. Gerencie sua equipe de forma eficiente e segura.'
            },
            {
                icon: '📄',
                title: 'Gestão de Documentos',
                description: 'Organize, armazene e compartilhe documentos corporativos com facilidade. Sistema de categorização inteligente.'
            },
            {
                icon: '📅',
                title: 'Gestão de Eventos',
                description: 'Planeje, organize e acompanhe eventos organizacionais. Calendário integrado com notificações automáticas.'
            },
            {
                icon: '📊',
                title: 'Dashboard Inteligente',
                description: 'Visualize métricas importantes e tome decisões baseadas em dados. Relatórios detalhados e análises em tempo real.'
            }
        ];
        
        $scope.goToLogin = function() {
            $location.path('/login');
        };
        
        $scope.scrollToFeatures = function() {
            var element = $document[0].getElementById('features');
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };
    }]);

