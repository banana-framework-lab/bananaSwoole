/*
 Navicat Premium Data Transfer

 Source Server         : 贪玩测试
 Source Server Type    : MySQL
 Source Server Version : 50727
 Source Host           : 192.168.5.106:3306
 Source Schema         : swoole_vue_admin

 Target Server Type    : MySQL
 Target Server Version : 50727
 File Encoding         : 65001

 Date: 17/11/2019 11:16:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sva_admin
-- ----------------------------
DROP TABLE IF EXISTS `sva_admin`;
CREATE TABLE `sva_admin`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '唯一id标识',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '账号',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '昵称',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '姓名',
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '头像',
  `role_id` int(10) UNSIGNED NOT NULL COMMENT '角色id',
  `create_time` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL COMMENT '资料更新时间',
  `last_login_time` int(10) UNSIGNED NOT NULL COMMENT '最后一次登录时间',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否禁用:1可用，-1禁用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sva_admin
-- ----------------------------
INSERT INTO `sva_admin` VALUES (1, 'admin', '造物者', '天神', '123456', '1', 1, 1, 1, 1, 1);

-- ----------------------------
-- Table structure for sva_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `sva_admin_role`;
CREATE TABLE `sva_admin_role`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色说明',
  `route_list` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色拥有的路由id集合(用,进行切割记录)',
  `button_list` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色拥有的按钮id集合(用,进行切割记录)',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '角色可用状态:1可用,-1不可用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sva_admin_role_button
-- ----------------------------
DROP TABLE IF EXISTS `sva_admin_role_button`;
CREATE TABLE `sva_admin_role_button`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `router_id` int(10) UNSIGNED NOT NULL COMMENT '按钮所属路由id',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '按钮名称',
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 2 COMMENT '按钮类型，1默认拥有类型，2需要分配类型',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '按钮的可用状态，1可用-1不可用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sva_admin_role_route
-- ----------------------------
DROP TABLE IF EXISTS `sva_admin_role_route`;
CREATE TABLE `sva_admin_role_route`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  `pid` int(10) UNSIGNED NOT NULL COMMENT '父级id，如果是0则为父级路由',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '路由名称',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '路由路径(uri)',
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 2 COMMENT '路由类型，1默认拥有类型，2需要分配类型',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '路由的可用状态，1可用-1不可用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sva_admin_role_table
-- ----------------------------
DROP TABLE IF EXISTS `sva_admin_role_table`;
CREATE TABLE `sva_admin_role_table`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '唯一id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
