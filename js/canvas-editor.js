/**
 * @typedef Vector
 * @type {object}
 * @property {number} x
 * @property {number} y
 */

/**
 * @typedef Layer
 * @type {object}
 * @property {string} id - Layer ID.
 * @property {Vector} position - Layer Position.
 * @property {Vector} dimensions - Layer Dimensions.
 */

/**
 * @abstract
 * @class
 */
class Command {
	/**
	 * Execute the command.
	 *
	 * @abstract
	 * @param {CanvasEditor} editor
	 */
	execute(editor) {
		throw new Error("Abstract method execute(editor) must be implemented");
	}

	/**
	 * Undo the command.
	 *
	 * @abstract
	 * @param {CanvasEditor} editor
	 */
	undo(editor) {
		throw new Error("Abstract method undo(editor) must be implemented");
	}
}

/**
 * @class
 * @extends Command
 */
class DrawCommand extends Command {
	/**
	 * Draw an image on a new layer.
	 *
	 * @param {HTMLImageElement} image
	 * @param {Vector} position
	 * @param {Vector} dimensions
	 */
	constructor(image, position, dimensions) {
		super();

		/** @type {HTMLImageElement} */
		this.image = image;
		/** @type {Vector} */
		this.position = position;
		/** @type {Vector} */
		this.dimensions = dimensions;
		/** @type {string | undefined} */
		this.layerId = undefined;
	}

	/**
	 * Execute the command.
	 *
	 * @param {CanvasEditor} editor
	 */
	execute(editor) {
		this.layerId = editor.addLayer(
			this.image,
			this.position,
			this.dimensions,
			this.layerId
		);
		editor.selectLayer(this.layerId);

		editor.redraw();
	}

	/**
	 * Undo the command.
	 *
	 * @param {CanvasEditor} editor
	 */
	undo(editor) {
		editor.removeLayer(this.layerId);

		editor.redraw();
	}
}

/**
 * @class
 * @extends Command
 */
class DeleteCommand extends Command {
	/**
	 * Delete a layer.
	 *
	 * @param {string} layerId
	 */
	constructor(layerId) {
		super();

		/** @type {string | undefined} */
		this.layerId = layerId;
		/** @type {Layer | undefined} */
		this.layer = undefined;
	}

	/**
	 *
	 * @param {CanvasEditor} editor
	 */
	execute(editor) {
		this.layer = editor.removeLayer(this.layerId);

		editor.redraw();
	}

	/**
	 *
	 * @param {CanvasEditor} editor
	 */
	undo(editor) {
		editor.addLayer(
			this.layer.image,
			this.layer.position,
			this.layer.dimensions,
			this.layer.id
		);

		this.layer = undefined;

		editor.selectLayer(this.layerId);

		editor.redraw();
	}
}

class TransformCommand extends Command {
	/**
	 * Move a layer to a new position
	 *
	 * @param {string} layerId
	 * @param {Vector} initialPosition
	 * @param {Vector} initialDimensions
	 */
	constructor(layerId, initialPosition, initialDimensions) {
		super();

		/** @type {string | undefined} */
		this.layerId = layerId;
		/** @type {Vector} */
		this.initialPosition = structuredClone(initialPosition);
		/** @type {Vector} */
		this.initialDimensions = structuredClone(initialDimensions);
		/** @type {Vector} */
		this.translation = { x: 0, y: 0 };
		/** @type {Vector} */
		this.scale = { x: 1, y: 1 };
	}

	/**
	 * Update the translation.
	 *
	 * @param {Vector} translation
	 */
	setTranslation(translation) {
		this.translation = structuredClone(translation);
	}

	/**
	 * Update the scale.
	 *
	 * @param {Vector} scale
	 */
	setScale(scale) {
		this.scale = structuredClone(scale);
	}

	/**
	 * Execute the command.
	 *
	 * @param {CanvasEditor} editor
	 */
	execute(editor) {
		const layer = editor.selectLayer(this.layerId);

		if (!layer) return;

		layer.position.x = this.initialPosition.x + this.translation.x;
		layer.position.y = this.initialPosition.y + this.translation.y;

		layer.dimensions.x = this.initialDimensions.x * this.scale.x;
		layer.dimensions.y = this.initialDimensions.y * this.scale.y;

		editor.redraw();
	}

	/**
	 * Undo the command.
	 *
	 * @param {CanvasEditor} editor
	 */
	undo(editor) {
		const layer = editor.selectLayer(this.layerId);

		if (!layer) return;

		layer.position.x = this.initialPosition.x;
		layer.position.y = this.initialPosition.y;

		layer.dimensions.x = this.initialDimensions.x;
		layer.dimensions.y = this.initialDimensions.y;

		editor.redraw();
	}
}

const cornerSize = 10;

/** @type {Vector[]} */
const corners = [
	{ x: 0, y: 0 },
	{ x: 0, y: 1 },
	{ x: 1, y: 0 },
	{ x: 1, y: 1 },
];

class CanvasEditor {
	/**
	 * @param {HTMLCanvasElement} canvas
	 */
	constructor(canvas) {
		/** @type {HTMLCanvasElement} */
		this.canvas = canvas;
		/** @type {CanvasRenderingContext2D} */
		this.context = canvas.getContext("2d");

		/** @type {HTMLImageElement | undefined} */
		this.background = undefined;

		/** @type {Layer[]} */
		this.layers = [];
		/** @type {Layer | undefined} */
		this.selectedLayer = undefined;

		/** @type {Command[]} */
		this.undoStack = [];
		/** @type {Command[]} */
		this.redoStack = [];

		this.context.imageSmoothingEnabled = false;

		this._setupEventListeners();
	}

	/**
	 * Execute a command.
	 * @param {DrawCommand} command
	 */
	executeCommand(command) {
		command.execute(this);
		this.undoStack.push(command);
		this.redoStack = [];
	}

	/** Undo the previous command */
	undo() {
		if (this.undoStack.length > 0) {
			const command = this.undoStack.pop();
			command.undo(this);
			this.redoStack.push(command);
		}
	}

	/**
	 * Redo the previous undone command.
	 */
	redo() {
		if (this.redoStack.length > 0) {
			const command = this.redoStack.pop();
			command.execute(this);
			this.undoStack.push(command);
		}
	}

	/**
	 * Set the background image.
	 *
	 * @param {HTMLElement} image
	 * @param {number} sourceX
	 * @param {number} sourceY
	 * @param {number} width
	 * @param {number} height
	 */
	setBackgroundImage(image, sourceX, sourceY, width, height) {
		this.context.drawImage(
			image,
			sourceX,
			sourceY,
			width,
			height,
			0,
			0,
			this.canvas.width,
			this.canvas.height
		);

		const src = this.canvas.toDataURL();

		const img = new Image();

		img.addEventListener("load", (_) => {
			this.background = img;

			this.redraw();
		});

		img.src = src;
	}

	/**
	 * Select the layer at the given index and order it to the top.
	 *
	 * If the layer index is -1, the selection will be undefined.
	 *
	 * @param {number} layerIndex
	 * @returns {Layer | undefined}
	 */
	_selectLayer(layerIndex) {
		if (layerIndex !== -1) {
			this.selectedLayer = this.layers.splice(layerIndex, 1)[0];
			this.layers.push(this.selectedLayer);
		} else {
			this.selectedLayer = undefined;
		}

		return this.selectedLayer;
	}

	/**
	 * Select a layer at the given position.
	 *
	 * If the position is not contained in any layer,
	 * the selection will be unset.
	 *
	 * @param {Vector} position
	 * @returns {Layer | undefined} Selected layer.
	 */
	selectLayerAt(position) {
		const layerIndex = this.layers.findLastIndex((layer) => {
			const layerLeft = Math.min(
				layer.position.x,
				layer.position.x + layer.dimensions.x
			);
			const layerRight = Math.max(
				layer.position.x,
				layer.position.x + layer.dimensions.x
			);
			const layerTop = Math.min(
				layer.position.y,
				layer.position.y + layer.dimensions.y
			);
			const layerBottom = Math.max(
				layer.position.y,
				layer.position.y + layer.dimensions.y
			);

			return (
				position.x >= layerLeft &&
				position.x <= layerRight &&
				position.y >= layerTop &&
				position.y <= layerBottom
			);
		});

		return this._selectLayer(layerIndex);
	}

	/**
	 * Select a layer by id.
	 *
	 * If the layer does not exist,
	 * the selection will be unset.
	 *
	 * @param {string} layerId
	 * @returns {Layer | undefined} Selected layer.
	 */
	selectLayer(layerId) {
		if (!this.selectedLayer || this.selectedLayer.id !== layerId) {
			const layerIndex = this.layers.findIndex((layer) => layer.id === layerId);

			this._selectLayer(layerIndex);
		}

		return this.selectedLayer;
	}

	/**
	 * Select a drag handle at the given position.
	 *
	 * If the position is not container in any drag handle,
	 * the corner will be unset.
	 *
	 * @param {Vector} position
	 * @returns {Vector | undefined} Drag handle corner factors.
	 */
	selectDragHandle(position) {
		const layer = this.selectedLayer || this.selectLayerAt(position);

		if (!layer) return;

		for (const corner of corners) {
			const rectPosition = {
				x: layer.position.x + corner.x * layer.dimensions.x,
				y: layer.position.y + corner.y * layer.dimensions.y,
			};

			const rect = {
				left: rectPosition.x - cornerSize / 2 - 1,
				right: rectPosition.x + cornerSize / 2 + 2,
				top: rectPosition.y - cornerSize / 2 - 1,
				bottom: rectPosition.y + cornerSize / 2 + 2,
			};

			if (
				position.x >= rect.left &&
				position.x <= rect.right &&
				position.y >= rect.top &&
				position.y <= rect.bottom
			) {
				return corner;
			}
		}

		return undefined;
	}

	/**
	 * Deselect layer.
	 */
	deselectLayer() {
		this.selectedLayer = undefined;
	}

	/**
	 * Get the position relative to the canvas from a drag event.
	 *
	 * @param {DragEvent} e
	 */
	_getRelativeClientPosition(e) {
		const canvasRect = canvas.getBoundingClientRect();

		const x = ((e.clientX - canvasRect.left) * canvas.width) / canvasRect.width;
		const y =
			((e.clientY - canvasRect.top) * canvas.height) / canvasRect.height;

		return { x, y };
	}

	_setupEventListeners() {
		document.addEventListener("keydown", (e) => {
			if (e.ctrlKey) {
				switch (e.key) {
					case "z":
						this.undo();
						break;

					case "y":
						this.redo();
						break;

					default:
						console.debug("Unknown control key combination:", e.key);
						break;
				}
			} else {
				switch (e.key) {
					case "Delete":
						if (this.selectedLayer) {
							this.executeCommand(new DeleteCommand(this.selectedLayer.id));
						}
						break;
				}
			}
		});

		this.canvas.addEventListener("dragover", (e) => {
			e.preventDefault();
		});

		/**
		 * @param {DragEvent} e
		 */
		const onDrop = (e) => {
			e.preventDefault();

			const files = e.dataTransfer.files;

			var dropX = e.offsetX;
			var dropY = e.offsetY;

			var dragOffsetX = 0;
			var dragOffsetY = 0;

			var scaledWidth = 0;
			var scaledHeight = 0;

			const img = new Image();

			img.onload = (e) => {
				const canvasRect = canvas.getBoundingClientRect();

				const canvasScale = canvas.width / canvasRect.width;

				const width = scaledWidth * canvasScale;
				const height = scaledHeight * canvasScale;

				const x = (dropX - dragOffsetX) * canvasScale;
				const y = (dropY - dragOffsetY) * canvasScale;

				this.executeCommand(
					new DrawCommand(img, { x, y }, { x: width, y: height })
				);
			};

			if (files.length === 1 && files[0].type.startsWith("image/")) {
				const file = files[0];
				const reader = new FileReader();

				reader.onload = (e) => {
					img.src = e.target.result;
				};

				reader.readAsDataURL(file);
			} else {
				try {
					const { offsetX, offsetY, width, height } = JSON.parse(
						e.dataTransfer.getData("application/sticker")
					);

					dragOffsetX = offsetX;
					dragOffsetY = offsetY;

					scaledWidth = width;
					scaledHeight = height;
				} catch {
					console.debug("Unsupported drop:", e, files);
					return;
				}

				img.src = e.dataTransfer.getData("text/uri-list");
			}
		};

		this.canvas.addEventListener("drop", onDrop);

		/** @type {TransformCommand | undefined} */
		var transformCommand = undefined;
		/** @type {Vector | undefined} */
		var dragOrigin = undefined;
		/** @type {Vector | undefined} */
		var draggedCorner = undefined;

		/**
		 * @param {DragEvent} e
		 */
		const onHandleDragUpdate = (e) => {
			const position = this._getRelativeClientPosition(e);

			const delta = {
				x: position.x - dragOrigin.x,
				y: position.y - dragOrigin.y,
			};

			if (draggedCorner.x === 0) {
				transformCommand.setTranslation({
					x: delta.x,
					y: transformCommand.translation.y,
				});

				transformCommand.setScale({
					x: 1 - delta.x / transformCommand.initialDimensions.x,
					y: transformCommand.scale.y,
				});
			} else {
				// this.selectedLayer.dimensions.x += delta.x;
				transformCommand.setScale({
					x: 1 + delta.x / transformCommand.initialDimensions.x,
					y: transformCommand.scale.y,
				});
			}

			if (draggedCorner.y === 0) {
				transformCommand.setTranslation({
					x: transformCommand.translation.x,
					y: delta.y,
				});
				transformCommand.setScale({
					x: transformCommand.scale.x,
					y: 1 - delta.y / transformCommand.initialDimensions.y,
				});
			} else {
				transformCommand.setScale({
					x: transformCommand.scale.x,
					y: 1 + delta.y / transformCommand.initialDimensions.y,
				});
			}

			transformCommand.execute(this);
		};

		/**
		 * @param {Layer} layer
		 * @param {Vector} position
		 * @param {Vector} corner
		 */
		const onHandleDragStart = (layer, position, corner) => {
			dragOrigin = position;
			draggedCorner = corner;

			transformCommand = new TransformCommand(
				layer.id,
				layer.position,
				layer.dimensions
			);

			this.canvas.addEventListener("mouseup", onHandleDragEnd);
			this.canvas.addEventListener("mousemove", onHandleDragUpdate);
		};

		const onHandleDragEnd = () => {
			this.canvas.removeEventListener("mousemove", onHandleDragUpdate);
			this.canvas.removeEventListener("mouseup", onHandleDragEnd);

			if (
				transformCommand.translation.x ||
				transformCommand.translation.y ||
				transformCommand.scale.x !== 0 ||
				transformCommand.scale.y !== 0
			) {
				this.executeCommand(transformCommand);
			}

			transformCommand = undefined;
		};

		const onLayerDragUpdate = (e) => {
			const position = this._getRelativeClientPosition(e);

			/** @type {Vector} */
			const delta = {
				x: position.x - dragOrigin.x,
				y: position.y - dragOrigin.y,
			};

			transformCommand.setTranslation({
				x: delta.x,
				y: delta.y,
			});

			transformCommand.execute(this);
		};

		const onLayerDragStart = (layer, position) => {
			dragOrigin = position;
			transformCommand = new TransformCommand(
				layer.id,
				layer.position,
				layer.dimensions
			);

			this.canvas.addEventListener("mouseup", onLayerDragEnd);
			this.canvas.addEventListener("mouseleave", onLayerDragEnd);
			this.canvas.addEventListener("mousemove", onLayerDragUpdate);
		};

		const onLayerDragEnd = () => {
			// console.debug("Layer drag end");
			this.canvas.removeEventListener("mousemove", onLayerDragUpdate);
			this.canvas.removeEventListener("mouseup", onLayerDragEnd);
			this.canvas.removeEventListener("mouseleave", onLayerDragEnd);

			if (transformCommand.translation.x || transformCommand.translation.y) {
				this.executeCommand(transformCommand);
			}

			dragOrigin = undefined;
			transformCommand = undefined;
		};

		const onCanvasClick = (e) => {
			// console.debug("Canvas mouse down:", e);

			const position = this._getRelativeClientPosition(e);

			const corner = this.selectDragHandle(position);

			if (corner) {
				onHandleDragStart(this.selectedLayer, position, corner);
			} else {
				this.selectLayerAt(position);

				this.redraw();

				if (this.selectedLayer) {
					onLayerDragStart(this.selectedLayer, position);
				}
			}
		};

		this.canvas.addEventListener("mousedown", onCanvasClick);
	}

	/**
	 * Add a layer.
	 *
	 * @param {HTMLImageElement} image
	 * @param {Vector} position
	 * @param {Vector} dimensions
	 * @param {string | undefined} id
	 * @return {string} the newly added layer's id
	 */
	addLayer(image, position, dimensions, id) {
		const layer = {
			id: id || crypto.randomUUID(),
			image,
			position,
			dimensions,
		};

		// console.debug("Added layer:", id, layer.id);

		this.layers.push(layer);

		return layer.id;
	}

	/**
	 * Remove a layer.
	 *
	 * @param {number} layerId
	 * @returns {Layer | undefined} the deleted layer
	 */
	removeLayer(layerId) {
		/** @type {Layer | undefined} */
		var deletedLayer;

		if (this.selectedLayer && this.selectedLayer.id === layerId) {
			deletedLayer = this.selectedLayer;
			this.selectedLayer = undefined;
		} else {
			deletedLayer = this.layers.find((layer) => layer.id === layerId);
		}

		if (deletedLayer) {
			this.layers = this.layers.filter((layer) => layer.id !== layerId);
			// console.debug(this.layers);
		}

		// console.debug(deletedLayer);

		return deletedLayer;
	}

	/**
	 * Draw drag handles and outline around the selected layer.
	 */
	_drawSelectionHandles() {
		const layer = this.selectedLayer;

		if (!layer) return;

		this.context.beginPath();
		this.context.setLineDash([10, 5]);
		this.context.strokeStyle = "gray";
		this.context.lineWidth = 1;

		this.context.globalCompositeOperation = "difference";

		this.context.strokeRect(
			layer.position.x - this.context.lineWidth,
			layer.position.y - this.context.lineWidth,
			layer.dimensions.x + 2 * this.context.lineWidth,
			layer.dimensions.y + 2 * this.context.lineWidth
		);

		this.context.globalCompositeOperation = "source-over";

		this.context.setLineDash([]);

		const primaryColor = getComputedStyle(document.documentElement)
			.getPropertyValue("--bs-primary")
			.trim();

		this.context.fillStyle = primaryColor;

		for (const corner of corners) {
			const x =
				layer.position.x -
				this.context.lineWidth +
				corner.x * (layer.dimensions.x + 2 * this.context.lineWidth) -
				cornerSize / 2;
			const y =
				layer.position.y -
				this.context.lineWidth +
				corner.y * (layer.dimensions.y + 2 * this.context.lineWidth) -
				cornerSize / 2;

			this.context.fillRect(x, y, cornerSize, cornerSize);

			this.context.globalCompositeOperation = "difference";
			this.context.strokeRect(x, y, cornerSize, cornerSize);
			this.context.globalCompositeOperation = "source-over";
		}
	}

	/**
	 * Draw background image.
	 */
	_drawBackground() {
		this.context.drawImage(
			this.background,
			0,
			0,
			this.canvas.width,
			this.canvas.height
		);
	}

	/**
	 *	Draw a single layer.
	 *
	 * @param {Layer} layer
	 */
	_drawLayer(layer) {
		const dimensionSigns = {
			x: Math.sign(layer.dimensions.x),
			y: Math.sign(layer.dimensions.y),
		};

		this.context.scale(
			dimensionSigns.x,
			dimensionSigns.y
		);

		this.context.drawImage(
			layer.image,
			dimensionSigns.x * layer.position.x,
			dimensionSigns.y * layer.position.y,
			dimensionSigns.x * layer.dimensions.x,
			dimensionSigns.y * layer.dimensions.y
		);

		this.context.setTransform(1, 0, 0, 1, 0, 0);
	}

	/**
	 * Redraw layers on the canvas.
	 */
	redraw() {
		this._drawBackground();

		for (const layer of this.layers) {
			this._drawLayer(layer);
		}

		this._drawSelectionHandles();
	}
}
