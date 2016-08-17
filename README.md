Configuration:
```neon
workflow:
	order:
		activities:
			new: 2 #START
			todo: 4
			must_order: 8
			waiting: 16
			shippable: 32
			shipped: 64 #END
			returned: 128  #END
		transitions:
			alwaysInvoke: @statusChanger
			matrix: [todo, must_order, waiting, shippable] #TODO: invoke
			- from: new
			  to: [todo, must_order, waiting, shippable, shipped, returned]
			  #invoke: ...
			- from: todo
			  to: [shipped, returned]
			- from: must_order
			  to: [shipped, returned]
			- from: waiting
			  to: [shipped, returned]
			- from: shippable
			  to: [shipped, returned]
```

Output transitions:
```
new         =>  todo
new         =>  must_order
new         =>  waiting
new         =>  shippable
new         =>  shipped
new         =>  returned
todo        =>  shipped
todo        =>  returned
must_order  =>  shipped
must_order  =>  returned
waiting     =>  shipped
waiting     =>  returned
shippable   =>  shipped
shippable   =>  returned
todo        =>  must_order
todo        =>  waiting
todo        =>  shippable
must_order  =>  todo
must_order  =>  waiting
must_order  =>  shippable
waiting     =>  todo
waiting     =>  must_order
waiting     =>  shippable
shippable   =>  todo
shippable   =>  must_order
shippable   =>  waiting
```
