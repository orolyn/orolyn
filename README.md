Orolyn PHP Library
==================

[![Build Status](https://travis-ci.org/orolyn/orolyn.svg?branch=master)](https://travis-ci.org/orolyn/orolyn)

A fiber based asynchronous tooling library for server development.

What is it
----------

This library provides general purpose tools for server development. The primary goal of this library is to enable
development with a familiar synchronous pattern while utilizing fibers to allow for multithreading-like operations.

Requirements
------------

- Linux operating system
- 64bit OS
- PHP 8.1+

Todo
----

- Tidy up helper functions
- Maybe make primitive types singletons
- Fill out collection library
- Find an alternative to EIO
- Rebuild the binary formatter
- Add tests for Net and Concurrency
- Remove unneeded dependencies
- Switch the base implementation for datagram sockets
- Really need some kind of central stdio class
- See if it's possible for `Await` to give way to other tasks outside the current chain.
