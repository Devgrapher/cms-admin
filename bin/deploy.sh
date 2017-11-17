#!/usr/bin/env bash

# Deploy to AWS ECS
export DOCKER_TAG=${TRAVIS_TAG:-latest}
if ! [ -x "$(command -v ecs-cli)" ]; then
    UNAME_RESULT=`uname`
    if [[ "${UNAME_RESULT}" == 'Darwin' ]]; then
       PLATFORM='darwin'
    else
       PLATFORM='linux'
    fi
    curl -o ecs-cli https://s3.amazonaws.com/amazon-ecs-cli/ecs-cli-${PLATFORM}-amd64-v1.0.0 && chmod +x ecs-cli
    ./ecs-cli compose up -c ${AWS_ECS_CLUSTER}
else
    ecs-cli compose up -c ${AWS_ECS_CLUSTER}
fi
