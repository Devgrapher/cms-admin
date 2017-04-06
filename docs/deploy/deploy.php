<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration

//symlink를 상대 주소로 생성하는 옵션. 현재 버그가 있어서 off
set('use_relative_symlink', false);

//shallow clone을 위한 옵션.
//이 옵션을 false하면 --depth 1 옵션을 추가하여 git clone을 받는다.
set('git_cache', false);

//web server user 지정.
set('http_user', 'www-data');

// Servers
foreach (glob(__DIR__ . '/servers/*.yml') as $filename) {
    serverList($filename);
}

/*

배포 이후 디렉토리 구조는 다음과 같이 구성된다.

배포시 releases 하위에 넘버가 하나씩 증가하며 생성되고 current가 가장 최신의 것을 가리킨다.
그러므로 실제 소스 위치는 deploy_path가 아니라 deploy_path/current를 참조해야 한다.

releases 하위에는 최대 keep_releases 옵션에 지정된 수 만큼 보관된다. (기본값=5)
롤백 시에는 바로 전 넘버로 변경되고 원래 current가 가리키던 디렉토리는 삭제된다.
계속 삭제되어 기록이 없으면 롤백이 실패한다.

.dep에 deploy:lock상태나 release history가 기록되어 rollback시 참조한다.
shared_files와 shared_dirs에 지정된 데로 shared 하위를 가리키는 symlink들을 생성한다.

/deploy_path
    /.dep
    /current -> releases/3
    /releases
        /1
            some_shared_file -> ../shared/some_shared_file
            some_shared_dir -> ../shared/some_shared_dir/
        /2
            some_shared_file -> ../shared/some_shared_file
            some_shared_dir -> ../shared/some_shared_dir/
        /3
            some_shared_file -> ../shared/some_shared_file
            some_shared_dir -> ../shared/some_shared_dir/
    /shared
        some_shared_file
        /some_shared_dir

*/

desc('Build client code');
task('deploy:build', 'make -C {{release_path}}');

desc('Deploy your project');
task('deploy', [
    'deploy:prepare', //기본 디렉터리 구조 생성
    'deploy:lock', //동시에 deploy가 진행되지 않도록 lock을 설정한다.
    'deploy:release', //{deploy_path}/releases/{release_name} 으로
    'deploy:update_code', //git clone을 실행한다.
    'deploy:shared', //shared_files, shared_dirs로 설정된 데로 current에 symlink를 생성한다.
    'deploy:writable', //http_user로 지정한 유저 권한으로 writable_dirs에 지정된 디텍토리들을 쓰기 가능하게 만든다.
    'deploy:vendors', //composer 모듈 설치
    'deploy:build', //client code 빌드
    'deploy:clear_paths', //clear_paths에 지정된 path들을 삭제한다.
    'deploy:symlink', //release된 디렉토리에 current로 symlink를 설정한다.
    'deploy:unlock', //deploy:lock을 해제한다. 이것이 실행되기 전에 도중 종료된 경우 직접 "dep deploy:unlock"으로 해제해주어야 다시 lock을 얻을 수 있다.
    'cleanup' //keep_releases 옵션을 넘는 release를 오래된 순으로 삭제한다. (default = 5)
]);
after('deploy', 'success');
