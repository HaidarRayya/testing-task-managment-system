<?php

namespace App\Enums;

enum UserPermission: string
{
    case GET_COMMENT  = "get-comment";
    case CREATE_COMMENT  = "create-comment";
    case UPDATE_COMMENT  = "update-comment";
    case DELETE_COMMENT  = "delete-comment";
    case GET_ATTACHMENT  = "get-attachment";
    case CREATE_ATTACHMENT = "create-attachment";
    case UPDATE_ATTACHMENT  = "update-attachment";
    case DELETE_ATTACHMENT  = "delete-attachment";
    case DOWNLOAD_ATTACHMENT = "download-attachment";
    case GET_TASK  = "get-task";
    case CREATE_TASK  = "create-task";
    case UPDATE_TASK  = "update-task";
    case DELETE_TASK  = "delete-task";
    case ASSIGN_TASK  = "assign-task";
    case REASSIGN_TASK  = "reassign-task";
    case START_WORK_TASK  = "start-work-task";
    case END_WORK_TASK  = "end-work-task";
    case START_TEST_TASK  = "start-test-task";
    case END_TEST_TASK  = "end-test-task";
    case END_TASK = "end-task";
    case RESTORE_TASK = "restore-task";
    case GET_DELELTED_TASK = "get-deleted-task";
    case CREATE_REPORTS  = "create-reports";
    case GET_USER  = "get-user";
    case CREATE_USER  = "create-user";
    case UPDATE_USER  = "update-user";
    case DELETE_USER  = "delete-user";
    case RESTORE_USER = "restore-user";
    case GET_DELELTED_USER = "get-deleted-user";
    case FINAL_DELETE_USER = "filal-delete-user";
}